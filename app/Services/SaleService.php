<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private readonly LedgerService $ledger)
    {
    }

    /**
     * Create a sale, its items, and reduce stock, all in one transaction.
     * Prices always come from the database, never from the browser.
     *
     * `exchange_credit` (optional) is set only by ExchangeService: it is the value of goods the
     * customer handed back, which pays for part of this sale. The customer then only has to
     * pay `total - exchange_credit` in real money.
     *
     * Credit sales (udhaar): when `paid` is less than what is due, the shortfall is put on the
     * customer's account. That needs a registered customer (`customer_id`). The shortfall is written
     * to the ledger as a `sale_credit` entry in the same transaction as the sale itself.
     * If the new balance would pass the customer's credit limit the sale is refused, unless the
     * caller sets `allow_over_limit` (the controller only does that for admin/manager).
     *
     * @param  array{items: array<int, array{product_id:int, quantity:float|int|string}>, discount?:mixed, paid:mixed, payment_method:string, customer_id?:?int, customer_name?:?string, note?:?string, exchange_credit?:mixed, allow_over_limit?:bool}  $data
     */
    public function create(array $data, ?int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $priced = $this->price($data['items'], $data['discount'] ?? 0);

            $rows     = $priced['rows'];
            $subtotal = $priced['subtotal'];
            $discount = $priced['discount'];
            $total    = $priced['total'];

            $credit   = round(min(max((float) ($data['exchange_credit'] ?? 0), 0.0), $total), 2);
            $due      = round($total - $credit, 2);
            $paid     = round((float) $data['paid'], 2);
            $onCredit = round(max($due - $paid, 0.0), 2);   // the part that goes on the customer's account
            $method   = $data['payment_method'];

            // Lock the customer first, so the balance we read cannot change before we write the entry
            $customer = $this->lockCustomer($data['customer_id'] ?? null);

            $this->assertCreditAllowed($method, $onCredit, $customer);

            $previousBalance = $customer ? $this->ledger->currentBalance($customer) : 0.0;
            $overLimit       = $onCredit > 0 && $this->exceedsCreditLimit($customer, $previousBalance, $onCredit);

            if ($overLimit && ! ($data['allow_over_limit'] ?? false)) {
                throw $this->creditLimitException($customer, $previousBalance, $onCredit);
            }

            $sale = Sale::create([
                'invoice_no'       => $this->nextInvoiceNo(),
                'user_id'          => $userId,
                'customer_id'      => $customer?->id,
                // A registered customer's name is copied onto the sale, so old receipts never change
                'customer_name'    => $customer?->name ?? ($data['customer_name'] ?? null),
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'total'            => $total,
                'paid'             => $paid,
                'change_amount'    => round(max($paid - $due, 0.0), 2),
                'credit_amount'    => $onCredit,
                'previous_balance' => $previousBalance,
                'exchange_credit'  => $credit,
                'payment_method'   => $method,
                'status'           => 'completed',
                'note'             => $data['note'] ?? null,
                'sold_at'          => now(),
            ]);

            foreach ($rows as $row) {
                /** @var Product $product */
                $product = $row['product'];

                $sale->items()->create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'barcode'        => $product->barcode,
                    'quantity'       => $row['qty'],
                    'unit_price'     => $row['unit_price'],
                    'purchase_price' => $row['purchase_price'],
                    'line_total'     => $row['line_total'],
                ]);

                // save() (not decrement) so the product is queued for cloud sync
                $product->quantity = round((float) $product->quantity - $row['qty'], 3);
                $product->save();
            }

            if ($onCredit > 0) {
                $this->ledger->charge(
                    $customer,
                    $onCredit,
                    'sale',
                    $sale->uuid,
                    "Invoice {$sale->invoice_no}" . ($overLimit ? ' (credit limit overridden)' : ''),
                    $userId,
                );
            }

            return $sale;
        });
    }

    private function lockCustomer(mixed $customerId): ?Customer
    {
        if (blank($customerId)) {
            return null;
        }

        return Customer::query()->lockForUpdate()->findOrFail($customerId);
    }

    /** A sale may only leave money unpaid when a registered, active customer will owe it. */
    private function assertCreditAllowed(string $method, float $onCredit, ?Customer $customer): void
    {
        if ($method === Sale::CREDIT_METHOD && $onCredit <= 0) {
            throw ValidationException::withMessages([
                'paid' => 'Nothing is left on credit. Choose a normal payment method instead.',
            ]);
        }

        if ($onCredit <= 0) {
            return;
        }

        if ($customer === null) {
            // Keep the old `paid` error key for a plain short payment, so existing clients behave the same
            $key = $method === Sale::CREDIT_METHOD ? 'customer_id' : 'paid';

            throw ValidationException::withMessages([
                $key => $method === Sale::CREDIT_METHOD
                    ? 'Select a customer to sell on credit.'
                    : 'Paid amount is less than the total. Select a customer to put the balance on credit.',
            ]);
        }

        if (! $customer->is_active) {
            throw ValidationException::withMessages([
                'customer_id' => 'This customer is inactive and cannot take goods on credit.',
            ]);
        }
    }

    /** A NULL limit means "no limit"; a limit of 0 means "no credit at all". */
    private function exceedsCreditLimit(?Customer $customer, float $balance, float $onCredit): bool
    {
        if ($customer === null || $customer->credit_limit === null) {
            return false;
        }

        return round($balance + $onCredit, 2) > (float) $customer->credit_limit;
    }

    private function creditLimitException(Customer $customer, float $balance, float $onCredit): ValidationException
    {
        $shop = ShopSetting::current();

        return ValidationException::withMessages([
            'credit_limit' => sprintf(
                'Credit limit exceeded: %s may owe at most %s, but the balance after this sale would be %s.',
                $customer->name,
                $shop->money($customer->credit_limit),
                $shop->money($balance + $onCredit),
            ),
        ]);
    }

    /**
     * Work out the lines and totals of a basket from database prices and check stock.
     * Writes nothing. Used by create() and by ExchangeService (to know the total up front).
     *
     * @param  array<int, array{product_id:int, quantity:float|int|string}>  $items
     * @return array{rows: array<int, array<string, mixed>>, subtotal: float, discount: float, total: float}
     */
    public function price(array $items, mixed $discount = 0): array
    {
        // Merge duplicate lines of the same product
        $lines = collect($items)
            ->groupBy('product_id')
            ->map(fn ($group) => round((float) $group->sum('quantity'), 3));

        $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

        $subtotal = 0.0;
        $rows = [];

        foreach ($lines as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => 'A product in the cart is no longer available.',
                ]);
            }

            if (round((float) $product->quantity, 3) < $qty) {
                throw ValidationException::withMessages([
                    'items' => "Not enough stock for \"{$product->name}\" (available: {$product->qty()}).",
                ]);
            }

            $lineTotal = round($qty * (float) $product->selling_price, 2);
            $subtotal += $lineTotal;

            $rows[] = [
                'product'        => $product,
                'qty'            => $qty,
                'unit_price'     => (float) $product->selling_price,
                'purchase_price' => (float) $product->purchase_price,
                'line_total'     => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discount = min(round((float) $discount, 2), $subtotal);

        return [
            'rows'     => $rows,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => round($subtotal - $discount, 2),
        ];
    }

    /** INV-20261116-0001 (the counter restarts every day) */
    private function nextInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $last = Sale::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}