<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    /**
     * Return (part of) a sale: record the return, put the goods back on the
     * shelf and work out the money to hand back, all in one transaction.
     *
     * Rules:
     *  - the original sale is never edited, except its status badge;
     *  - quantities are limited to "sold - already returned";
     *  - the refund uses the price the customer really paid (original unit
     *    price minus its share of the bill discount), never today's price;
     *  - stock goes up only for lines flagged "restock" and whose product still exists.
     *
     * @param  array{items: array<int, array{sale_item_id:int, quantity:float|int|string|null, restock?:mixed}>, refund_method?:?string, reason?:?string, note?:?string}  $data
     */
    public function create(Sale $sale, array $data, ?int $userId): SaleReturn
    {
        return DB::transaction(function () use ($sale, $data, $userId) {
            // Re-read the sale inside the transaction so two returns cannot race each other
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->getKey());

            $this->assertReturnable($sale);

            $states    = $this->lineStates($sale);
            $requested = $this->normalise($data['items'] ?? []);

            if ($requested->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a quantity for at least one item to return.',
                ]);
            }

            $rows = [];

            foreach ($requested as $saleItemId => $req) {
                $state = $states->get($saleItemId);

                if (! $state) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected items does not belong to this sale.',
                    ]);
                }

                /** @var SaleItem $item */
                $item = $state['item'];

                if ($req['qty'] > $state['available'] + 0.0005) {
                    throw ValidationException::withMessages([
                        'items' => sprintf(
                            '"%s": only %s left to return (sold %s, already returned %s).',
                            $item->product_name,
                            Product::trimNumber($state['available']),
                            Product::trimNumber($state['sold']),
                            Product::trimNumber($state['returned']),
                        ),
                    ]);
                }

                $rows[] = [
                    'state'  => $state,
                    'qty'    => $req['qty'],
                    'refund' => $this->refundFor($state, $req['qty']),
                    'restock' => $req['restock'],
                ];
            }

            $total = $this->clampToSale($sale, $rows);

            $return = SaleReturn::create([
                'return_no'     => $this->nextReturnNo(),
                'sale_id'       => $sale->id,
                'user_id'       => $userId,
                'refund_total'  => $total,
                'refund_method' => ($data['refund_method'] ?? null) ?: $sale->payment_method,
                'reason'        => $data['reason'] ?? null,
                'note'          => $data['note'] ?? null,
                'returned_at'   => now(),
            ]);

            $products = Product::query()
                ->whereIn('id', collect($rows)->map(fn ($r) => $r['state']['item']->product_id)->filter()->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($rows as $row) {
                /** @var SaleItem $item */
                $item    = $row['state']['item'];
                $product = $item->product_id ? $products->get($item->product_id) : null;
                $restock = $row['restock'] && $product !== null;

                $return->items()->create([
                    'sale_item_id'   => $item->id,
                    'product_id'     => $product?->id,
                    'product_name'   => $item->product_name,
                    'barcode'        => $item->barcode,
                    'quantity'       => $row['qty'],
                    'unit_price'     => $item->unit_price,
                    'purchase_price' => $item->purchase_price,
                    'refund_amount'  => $row['refund'],
                    'restocked'      => $restock,
                ]);

                if ($restock) {
                    // save() (not increment) so the product is queued for cloud sync
                    $product->quantity = round((float) $product->quantity + $row['qty'], 3);
                    $product->save();
                }
            }

            $sale->status = $this->resolveStatus($sale);
            $sale->save();

            return $return->load('items');
        });
    }

    /**
     * Per sale line: what was sold, what already came back and what is still
     * returnable. Keyed by sale_item id. Also used by the return form.
     *
     * @return Collection<int, array{item: SaleItem, sold: float, returned: float, available: float, net_line: float, refunded: float}>
     */
    public function lineStates(Sale $sale): Collection
    {
        $items = $sale->items()->orderBy('id')->get();

        $returned = SaleReturnItem::query()
            ->whereIn('sale_item_id', $items->pluck('id'))
            ->selectRaw('sale_item_id, SUM(quantity) AS qty, SUM(refund_amount) AS amount')
            ->groupBy('sale_item_id')
            ->get()
            ->keyBy('sale_item_id');

        // Share of the bill discount that every line carries (1.0 = no discount)
        $subtotal = (float) $sale->subtotal;
        $ratio    = $subtotal > 0 ? (float) $sale->total / $subtotal : 1.0;

        return $items->mapWithKeys(function (SaleItem $item) use ($returned, $ratio) {
            $done = $returned->get($item->id);
            $sold = round((float) $item->quantity, 3);
            $back = round((float) ($done?->qty ?? 0), 3);

            return [$item->id => [
                'item'      => $item,
                'sold'      => $sold,
                'returned'  => $back,
                'available' => max(0.0, round($sold - $back, 3)),
                'net_line'  => round((float) $item->line_total * $ratio, 2),
                'refunded'  => round((float) ($done?->amount ?? 0), 2),
            ]];
        });
    }

    /** The discount-adjusted price of one unit, for display only. */
    public function netUnitPrice(array $state): float
    {
        return $state['sold'] > 0 ? round($state['net_line'] / $state['sold'], 4) : 0.0;
    }

    private function assertReturnable(Sale $sale): void
    {
        if ($sale->status === Sale::STATUS_RETURNED) {
            throw ValidationException::withMessages([
                'items' => 'This sale has already been fully returned.',
            ]);
        }

        $days = (int) config('pos.returns.window_days', 0);

        if ($days > 0 && $sale->sold_at && $sale->sold_at->lt(now()->subDays($days))) {
            throw ValidationException::withMessages([
                'items' => "Returns are only accepted within {$days} days of the sale.",
            ]);
        }
    }

    /**
     * Merge duplicate lines, drop empty ones.
     *
     * @return Collection<int, array{qty: float, restock: bool}>
     */
    private function normalise(array $items): Collection
    {
        return collect($items)
            ->groupBy(fn ($row) => (int) ($row['sale_item_id'] ?? 0))
            ->map(fn ($group) => [
                'qty'     => round((float) $group->sum(fn ($r) => (float) ($r['quantity'] ?? 0)), 3),
                'restock' => filter_var($group->last()['restock'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ])
            ->filter(fn ($row) => $row['qty'] > 0);
    }

    /**
     * Money back for $qty of one line. When the last remaining piece is
     * returned the exact remainder is refunded, so partial refunds always add
     * up to what the customer paid for the line (no lost or extra paisa).
     */
    private function refundFor(array $state, float $qty): float
    {
        $remaining = round($state['net_line'] - $state['refunded'], 2);

        if (abs($qty - $state['available']) < 0.0005) {
            return max(0.0, $remaining);
        }

        $share = $state['sold'] > 0 ? round($state['net_line'] * $qty / $state['sold'], 2) : 0.0;

        return max(0.0, min($remaining, $share));
    }

    /**
     * Never refund more than the customer paid for the whole sale. Per-line
     * rounding can overshoot by a few paisa, which is trimmed off the last line.
     */
    private function clampToSale(Sale $sale, array &$rows): float
    {
        $refundedBefore = round((float) $sale->returns()->sum('refund_total'), 2);
        $ceiling        = max(0.0, round((float) $sale->total - $refundedBefore, 2));
        $total          = round(array_sum(array_column($rows, 'refund')), 2);
        $excess         = round($total - $ceiling, 2);

        if ($excess > 0 && $rows) {
            $last                    = array_key_last($rows);
            $cut                     = min($excess, $rows[$last]['refund']);
            $rows[$last]['refund']   = round($rows[$last]['refund'] - $cut, 2);
            $total                   = round(array_sum(array_column($rows, 'refund')), 2);
        }

        return $total;
    }

    private function resolveStatus(Sale $sale): string
    {
        $allBack = $this->lineStates($sale)->every(fn (array $s) => $s['available'] <= 0);

        return $allBack ? Sale::STATUS_RETURNED : Sale::STATUS_PARTIALLY_RETURNED;
    }

    /** RET-20261121-0001 (the counter restarts every day) */
    private function nextReturnNo(): string
    {
        $prefix = 'RET-' . now()->format('Ymd') . '-';

        $last = SaleReturn::where('return_no', 'like', $prefix . '%')
            ->orderByDesc('return_no')
            ->value('return_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}