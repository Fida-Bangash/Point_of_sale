<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The only place that writes to customer_ledger.
 *
 * Sign convention (one rule, used everywhere):
 *   positive amount = the customer owes the shop more (udhaar)
 *   negative amount = the customer paid or was credited (wasooli)
 *   balance         = SUM(amount); positive means "customer owes us"
 *
 * Callers pass plain positive amounts; this class applies the sign.
 * Every write runs in a transaction, and entries are never edited or deleted.
 */
class LedgerService
{
    private const MAX_AMOUNT = 9999999999.99; // fits decimal(12,2)

    public function currentBalance(Customer $customer): float
    {
        return $customer->currentBalance();
    }

    /** Balance carried over from the old paper khata. Zero creates no entry. */
    public function openingBalance(Customer $customer, float|int|string $amount, ?int $userId = null): ?CustomerLedger
    {
        $amount = $this->money($amount);

        if ($amount === 0.0) {
            return null;
        }

        $this->assertInRange(abs($amount));

        return $this->record($customer, CustomerLedger::TYPE_OPENING, $amount, 'Opening balance', userId: $userId);
    }

    /** Sale on credit (udhaar): increases what the customer owes. */
    public function charge(
        Customer $customer,
        float|int|string $amount,
        ?string $refType = null,
        ?string $refUuid = null,
        ?string $note = null,
        ?int $userId = null,
    ): CustomerLedger {
        $amount = $this->positive($amount);

        if (! $customer->is_active) {
            throw ValidationException::withMessages([
                'customer_id' => 'This customer is inactive and cannot take goods on credit.',
            ]);
        }

        return $this->record(
            $customer, CustomerLedger::TYPE_SALE_CREDIT, $amount, $note,
            refType: $refType, refUuid: $refUuid, userId: $userId,
        );
    }

    /**
     * Payment received (wasooli): decreases what the customer owes.
     *
     * A payment may be larger than the balance. The balance then goes negative, which means the
     * customer has an advance with the shop. Callers that want to stop an accidental overpayment
     * (the controller does) must check that themselves before calling.
     *
     * The entry also stores the payment method, a receipt number and the balance just before it,
     * so the receipt can always be reprinted exactly as it was.
     */
    public function receivePayment(
        Customer $customer,
        float|int|string $amount,
        ?string $note = null,
        ?int $userId = null,
        ?CarbonInterface $date = null,
        ?string $method = null,
    ): CustomerLedger {
        $amount = $this->positive($amount);

        if ($method !== null && ! array_key_exists($method, CustomerLedger::PAYMENT_METHODS)) {
            throw ValidationException::withMessages(['payment_method' => 'Choose a valid payment method.']);
        }

        return DB::transaction(function () use ($customer, $amount, $note, $userId, $date, $method) {
            // Lock first, so the balance we read cannot change before we write the entry
            Customer::query()->whereKey($customer->getKey())->lockForUpdate()->firstOrFail();

            return $this->record(
                $customer, CustomerLedger::TYPE_PAYMENT, -$amount, $note,
                userId: $userId, date: $date,
                extra: [
                    'payment_method'   => $method,
                    'receipt_no'       => $this->nextReceiptNo(),
                    'previous_balance' => $this->currentBalance($customer),
                ],
            );
        });
    }

    /**
     * Refund of a returned sale put on the customer's account (return_credit): decreases what the
     * customer owes. It may push the balance below zero (an advance), exactly like an overpayment.
     * Inactive customers can still be credited, because the goods really came back.
     */
    public function returnCredit(
        Customer $customer,
        float|int|string $amount,
        ?string $refType = null,
        ?string $refUuid = null,
        ?string $note = null,
        ?int $userId = null,
    ): CustomerLedger {
        $amount = $this->positive($amount);

        return $this->record(
            $customer, CustomerLedger::TYPE_RETURN, -$amount, $note,
            refType: $refType, refUuid: $refUuid, userId: $userId,
        );
    }

    /** Manual correction with a mandatory reason. $amount is signed: + owes more, - owes less. */
    public function adjust(Customer $customer, float|int|string $amount, string $reason, ?int $userId = null): CustomerLedger
    {
        $amount = $this->money($amount);
        $reason = trim($reason);

        if ($amount === 0.0) {
            throw ValidationException::withMessages(['amount' => 'The adjustment amount cannot be zero.']);
        }

        if ($reason === '') {
            throw ValidationException::withMessages(['note' => 'A reason is required for an adjustment.']);
        }

        $this->assertInRange(abs($amount));

        return $this->record($customer, CustomerLedger::TYPE_ADJUSTMENT, $amount, $reason, userId: $userId);
    }

    /** Cancel an entry by writing its exact opposite. The original row is left untouched. */
    public function reverse(CustomerLedger $entry, string $reason, ?int $userId = null): CustomerLedger
    {
        return DB::transaction(function () use ($entry, $reason, $userId) {
            // Re-read inside the transaction so two reversals cannot race each other.
            $entry = CustomerLedger::query()->lockForUpdate()->findOrFail($entry->getKey());

            if ($entry->type === CustomerLedger::TYPE_REVERSAL) {
                throw ValidationException::withMessages(['entry' => 'A reversal cannot be reversed.']);
            }

            if ($entry->reversedBy()->exists()) {
                throw ValidationException::withMessages(['entry' => 'This entry has already been reversed.']);
            }

            $reason = trim($reason);

            return $this->record(
                $entry->customer,
                CustomerLedger::TYPE_REVERSAL,
                $this->money(-1 * (float) $entry->amount),
                $reason !== '' ? $reason : 'Reversal',
                refType: $entry->ref_type,
                refUuid: $entry->ref_uuid,
                reversalOfId: $entry->id,
                userId: $userId,
            );
        });
    }

    private function record(
        Customer $customer,
        string $type,
        float $amount,
        ?string $note = null,
        ?string $refType = null,
        ?string $refUuid = null,
        ?int $reversalOfId = null,
        ?int $userId = null,
        ?CarbonInterface $date = null,
        array $extra = [],
    ): CustomerLedger {
        return DB::transaction(function () use (
            $customer, $type, $amount, $note, $refType, $refUuid, $reversalOfId, $userId, $date, $extra
        ) {
            // Serialise writers per customer (a no-op on SQLite, which already has one writer).
            Customer::query()->whereKey($customer->getKey())->lockForUpdate()->firstOrFail();

            return CustomerLedger::create([
                'customer_id'    => $customer->getKey(),
                'type'           => $type,
                'amount'         => $amount,
                'reversal_of_id' => $reversalOfId,
                'ref_type'       => $refType,
                'ref_uuid'       => $refUuid,
                'note'           => $note !== null ? mb_substr(trim($note), 0, 500) : null,
                'user_id'        => $userId,
                'entry_date'     => $date ?? now(),
            ] + $extra);
        });
    }

    /** PAY-20261126-0001 (the counter restarts every day) */
    private function nextReceiptNo(): string
    {
        $prefix = 'PAY-' . now()->format('Ymd') . '-';

        $last = CustomerLedger::query()
            ->where('receipt_no', 'like', $prefix . '%')
            ->orderByDesc('receipt_no')
            ->value('receipt_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function money(float|int|string $value): float
    {
        return round((float) $value, 2);
    }

    private function positive(float|int|string $value): float
    {
        $amount = $this->money($value);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be greater than zero.']);
        }

        $this->assertInRange($amount);

        return $amount;
    }

    private function assertInRange(float $amount): void
    {
        if ($amount > self::MAX_AMOUNT) {
            throw ValidationException::withMessages(['amount' => 'The amount is too large.']);
        }
    }
}