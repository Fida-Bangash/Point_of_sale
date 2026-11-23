<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Create a sale, its items, and reduce stock, all in one transaction.
     * Prices always come from the database, never from the browser.
     *
     * `exchange_credit` (optional) is set only by ExchangeService: it is the value of goods the
     * customer handed back, which pays for part of this sale. The customer then only has to
     * pay `total - exchange_credit` in real money.
     *
     * @param  array{items: array<int, array{product_id:int, quantity:float|int|string}>, discount?:mixed, paid:mixed, payment_method:string, customer_name?:?string, note?:?string, exchange_credit?:mixed}  $data
     */
    public function create(array $data, ?int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $priced = $this->price($data['items'], $data['discount'] ?? 0);

            $rows     = $priced['rows'];
            $subtotal = $priced['subtotal'];
            $discount = $priced['discount'];
            $total    = $priced['total'];

            $credit = round(min(max((float) ($data['exchange_credit'] ?? 0), 0.0), $total), 2);
            $due    = round($total - $credit, 2);
            $paid   = round((float) $data['paid'], 2);

            if ($paid < $due) {
                throw ValidationException::withMessages([
                    'paid' => 'Paid amount is less than the total.',
                ]);
            }

            $sale = Sale::create([
                'invoice_no'      => $this->nextInvoiceNo(),
                'user_id'         => $userId,
                'customer_name'   => $data['customer_name'] ?? null,
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'total'           => $total,
                'paid'            => $paid,
                'change_amount'   => round($paid - $due, 2),
                'exchange_credit' => $credit,
                'payment_method'  => $data['payment_method'],
                'status'          => 'completed',
                'note'            => $data['note'] ?? null,
                'sold_at'         => now(),
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

            return $sale;
        });
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