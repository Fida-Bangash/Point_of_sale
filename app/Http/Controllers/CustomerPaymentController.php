<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\ShopSetting;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Step 4: wasooli. Receiving a payment from a customer, printing its receipt, and cancelling it.
 * All ledger writes go through LedgerService; nothing here edits or deletes a ledger row.
 */
class CustomerPaymentController extends Controller
{
    /** Record a payment. Admin, manager and cashier may do this. */
    public function store(Request $request, Customer $customer, LedgerService $ledger)
    {
        $data = $request->validate([
            'amount'          => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_method'  => ['required', Rule::in(array_keys(CustomerLedger::PAYMENT_METHODS))],
            'payment_date'    => ['nullable', 'date', 'before_or_equal:today'],
            'note'            => ['nullable', 'string', 'max:500'],
            'confirm_advance' => ['nullable', 'boolean'],
        ], [
            'payment_date.before_or_equal' => 'The payment date cannot be in the future.',
        ]);

        $amount  = round((float) $data['amount'], 2);
        $balance = $customer->currentBalance();

        // Paying more than the customer owes is allowed (the extra stays as an advance),
        // but only when the cashier explicitly confirms it, so a typo cannot create one.
        if ($amount > max($balance, 0.0) && ! $request->boolean('confirm_advance')) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'This is %s more than the customer owes. Tick "keep the extra as advance" to continue.',
                    ShopSetting::current()->money($amount - max($balance, 0.0)),
                ),
            ]);
        }

        // A back-dated payment keeps the current time of day, so entries on one day stay in order
        $date = filled($data['payment_date'] ?? null)
            ? Carbon::parse($data['payment_date'])->setTimeFrom(now())
            : now();

        $entry = $ledger->receivePayment(
            $customer,
            $amount,
            filled($data['note'] ?? null) ? $data['note'] : null,
            $request->user()->id,
            $date,
            $data['payment_method'],
        );

        return redirect()
            ->route('customers.payments.receipt', [$customer, $entry])
            ->with('status', 'Payment received.');
    }

    /** Printable payment receipt: ?size=a4 for A4, default is 80mm thermal. */
    public function receipt(Request $request, Customer $customer, CustomerLedger $entry)
    {
        $this->assertPaymentOf($customer, $entry);

        // Only entries written by the payment form have a receipt number
        abort_if($entry->receipt_no === null, 404);

        $entry->load('user:id,name');

        return view('pages.payment-receipt', [
            'customer'    => $customer,
            'entry'       => $entry,
            'cancelledBy' => $entry->reversedBy()->with('user:id,name')->first(),
            'shop'        => ShopSetting::current(),
            'size'        => $request->query('size') === 'a4' ? 'a4' : '80mm',
        ]);
    }

    /** Cancel a payment by writing a reversal entry. Admin only (enforced in routes/web.php). */
    public function cancel(Request $request, Customer $customer, CustomerLedger $entry, LedgerService $ledger)
    {
        $this->assertPaymentOf($customer, $entry);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:400'],
        ]);

        // reverse() refuses a second cancellation of the same payment
        $ledger->reverse(
            $entry,
            'Cancelled ' . ($entry->receipt_no ?? 'payment') . ': ' . trim($data['reason']),
            $request->user()->id,
        );

        return back()->with('status', 'Payment cancelled. A reversal entry was added to the ledger.');
    }

    /** The entry must be a payment that belongs to this customer, otherwise the URL is wrong. */
    private function assertPaymentOf(Customer $customer, CustomerLedger $entry): void
    {
        abort_unless($entry->customer_id === $customer->id && $entry->isPayment(), 404);
    }
}
