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
     * @param  array{items: array<int, array{product_id:int, quantity:float|int|string}>, discount?:mixed, paid:mixed, payment_method:string, customer_name?:?string, note?:?string}  $data
     */
    public function create(array $data, ?int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            // Merge duplicate lines of the same product
            $lines = collect($data['items'])
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
            $discount = min(round((float) ($data['discount'] ?? 0), 2), $subtotal);
            $total    = round($subtotal - $discount, 2);
            $paid     = round((float) $data['paid'], 2);

            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'paid' => 'Paid amount is less than the total.',
                ]);
            }

            $sale = Sale::create([
                'invoice_no'     => $this->nextInvoiceNo(),
                'user_id'        => $userId,
                'customer_name'  => $data['customer_name'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid'           => $paid,
                'change_amount'  => round($paid - $total, 2),
                'payment_method' => $data['payment_method'],
                'status'         => 'completed',
                'note'           => $data['note'] ?? null,
                'sold_at'        => now(),
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