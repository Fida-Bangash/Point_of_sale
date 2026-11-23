<?php

namespace App\Services;

use App\Models\Exchange;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * An exchange is a return plus a new sale, tied together by one record:
 *
 *   1. old goods come back  -> ReturnService  (stock goes up, refund value worked out)
 *   2. new goods go out     -> SaleService    (stock goes down)
 *   3. the difference       -> the customer pays it, or the shop pays it back
 *
 * Everything runs in ONE transaction. If anything fails (for example the new item is out of
 * stock) nothing at all is saved. The return is made first so goods handed back are already
 * back on the shelf when the stock of the new goods is checked (useful when swapping a
 * product for the same product in another size).
 */
class ExchangeService
{
    public function __construct(
        private readonly ReturnService $returns,
        private readonly SaleService $sales,
    ) {
    }

    /**
     * @param  array{
     *     return_items: array<int, array{sale_item_id:int, quantity:mixed, restock?:mixed}>,
     *     new_items: array<int, array{product_id:int, quantity:mixed}>,
     *     discount?: mixed, paid?: mixed, payment_method?: ?string, reason?: ?string, note?: ?string
     * }  $data
     */
    public function create(Sale $original, array $data, ?int $userId): Exchange
    {
        return DB::transaction(function () use ($original, $data, $userId) {
            $original = Sale::query()->lockForUpdate()->findOrFail($original->getKey());

            $newItems = collect($data['new_items'] ?? [])
                ->filter(fn ($row) => (float) ($row['quantity'] ?? 0) > 0)
                ->values()
                ->all();

            if ($newItems === []) {
                throw ValidationException::withMessages([
                    'new_items' => 'Add at least one new item the customer is taking.',
                ]);
            }

            // 1. Old goods back. The refund is recorded as "exchange credit", not as cash.
            $return = $this->returns->create($original, [
                'items'         => $data['return_items'] ?? [],
                'refund_method' => 'exchange',
                'reason'        => ($data['reason'] ?? null) ?: 'Exchange',
                'note'          => $data['note'] ?? null,
            ], $userId);

            $returned = round((float) $return->refund_total, 2);

            // 2. Price the new goods (this also checks their stock, after the return restocked)
            $discount = $data['discount'] ?? 0;
            $priced   = $this->sales->price($newItems, $discount);
            $newTotal = $priced['total'];

            // 3. The money difference
            $credit = round(min($returned, $newTotal), 2); // returned goods pay for new goods
            $due    = round($newTotal - $credit, 2);       // customer still owes this
            $refund = round($returned - $credit, 2);       // shop owes this back to the customer

            $paid = isset($data['paid']) && $data['paid'] !== ''
                ? round((float) $data['paid'], 2)
                : $due;

            if ($paid < $due) {
                throw ValidationException::withMessages([
                    'paid' => 'The customer must pay at least ' . number_format($due, 2) . ' for this exchange.',
                ]);
            }

            $method = ($data['payment_method'] ?? null) ?: 'cash';

            $sale = $this->sales->create([
                'items'           => $newItems,
                'discount'        => $discount,
                'exchange_credit' => $credit,
                'paid'            => $paid,
                'payment_method'  => $method,
                'customer_name'   => $original->customer_name,
                'note'            => "Exchange for {$original->invoice_no}",
            ], $userId);

            $exchange = Exchange::create([
                'exchange_no'      => $this->nextExchangeNo(),
                'original_sale_id' => $original->id,
                'sale_return_id'   => $return->id,
                'new_sale_id'      => $sale->id,
                'user_id'          => $userId,
                'returned_value'   => $returned,
                'new_value'        => $newTotal,
                'credit_applied'   => $credit,
                'cash_in'          => $due,
                'cash_out'         => $refund,
                'payment_method'   => $method,
                'note'             => $data['note'] ?? null,
                'exchanged_at'     => now(),
            ]);

            return $exchange->load(['originalSale', 'newSale.items', 'saleReturn.items']);
        });
    }

    /** EXC-20261122-0001 (the counter restarts every day) */
    private function nextExchangeNo(): string
    {
        $prefix = 'EXC-' . now()->format('Ymd') . '-';

        $last = Exchange::where('exchange_no', 'like', $prefix . '%')
            ->orderByDesc('exchange_no')
            ->value('exchange_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}