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
    /** Refund method: take the money off the customer's account (khata) instead of paying it out. */
    public const REFUND_LEDGER = 'ledger';

    public function __construct(private readonly LedgerService $ledger)
    {
    }

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
     * Credit sales (udhaar): a customer who bought on credit has not paid for everything, so not
     * everything can be handed back as cash. The refund is split in two:
     *   - `ledger`: taken off the customer's account (a `return_credit` ledger entry), and
     *   - cash (or any normal method): only up to what the customer really paid for the sale.
     * Choosing the refund method `ledger` sends the whole refund to the account. For a cash refund of
     * a credit sale the unpaid part goes to the account automatically. See splitRefund().
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

            $total  = $this->clampToSale($sale, $rows);
            $method = $this->resolveMethod($sale, $data['refund_method'] ?? null);

            // An exchange settles its money later (settle()), once the value of the new goods is known
            $split = $method === 'exchange'
                ? ['ledger' => 0.0, 'cash' => 0.0, 'exchange' => 0.0]
                : $this->splitRefund($sale, $total, $method);

            $return = SaleReturn::create([
                'return_no'       => $this->nextReturnNo(),
                'sale_id'         => $sale->id,
                'user_id'         => $userId,
                'refund_total'    => $total,
                'ledger_amount'   => $split['ledger'],
                'exchange_amount' => 0,
                'refund_method'   => $method,
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

            // The ledger entry is written in the same transaction as the return itself
            $this->creditLedger($sale, $return, $split['ledger'], $userId);

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

    /**
     * Split the money of a return into ledger / cash / exchange credit. Writes nothing.
     *
     * `$applied` is the part of the return that pays for new goods in an exchange (0 for a plain
     * return). `$exceptReturnId` leaves one return out of the "already refunded" maths: the
     * exchange calls this after its return was created.
     *
     *   - method `ledger`        -> everything that is not exchange credit goes to the account
     *   - any other method       -> cash up to what the customer really paid in cash for the sale
     *                               (total - credit_amount) minus what earlier returns and this
     *                               exchange already used of it; the rest goes to the account
     *
     * A sale without credit has total - credit_amount = total, so its cash part is never reduced
     * and nothing changes for ordinary customers.
     *
     * @return array{ledger: float, cash: float, exchange: float}
     */
    public function splitRefund(Sale $sale, float $total, string $method, float $applied = 0.0, ?int $exceptReturnId = null): array
    {
        $applied = round(min(max($applied, 0.0), $total), 2);
        $rest    = round($total - $applied, 2);

        if ($rest <= 0) {
            return ['ledger' => 0.0, 'cash' => 0.0, 'exchange' => $applied];
        }

        if ($method === self::REFUND_LEDGER) {
            $ledger = $rest;
        } else {
            $prior = fn () => $sale->returns()->when($exceptReturnId, fn ($q) => $q->where('id', '!=', $exceptReturnId));

            $paidInCash = max(0.0, round((float) $sale->total - (float) $sale->credit_amount, 2));
            $alreadyOut = round((float) $prior()->sum('refund_total') - (float) $prior()->sum('ledger_amount'), 2);
            $ceiling    = max(0.0, round($paidInCash - $alreadyOut - $applied, 2));

            $ledger = round(max($rest - $ceiling, 0.0), 2);
        }

        if ($ledger > 0 && $sale->customer_id === null) {
            throw ValidationException::withMessages([
                'refund_method' => 'This sale has no registered customer, so the refund cannot go to a customer account. Refund it in cash instead.',
            ]);
        }

        return ['ledger' => $ledger, 'cash' => round($rest - $ledger, 2), 'exchange' => $applied];
    }

    /**
     * Used by ExchangeService once it knows how much of the returned value pays for the new goods.
     * Writes the ledger part on the return, plus its ledger entry.
     *
     * @return array{ledger: float, cash: float, exchange: float}
     */
    public function settle(SaleReturn $return, Sale $sale, float $applied, string $refundTo, ?int $userId): array
    {
        $split = $this->splitRefund(
            $sale, (float) $return->refund_total,
            $refundTo === self::REFUND_LEDGER ? self::REFUND_LEDGER : 'cash',
            $applied, $return->id,
        );

        $return->ledger_amount   = $split['ledger'];
        $return->exchange_amount = round((float) $return->refund_total - $split['ledger'], 2);
        $return->save();

        $this->creditLedger($sale, $return, $split['ledger'], $userId);

        return $split;
    }

    private function creditLedger(Sale $sale, SaleReturn $return, float $amount, ?int $userId): void
    {
        if ($amount <= 0 || $sale->customer_id === null) {
            return;
        }

        $this->ledger->returnCredit(
            $sale->customer()->firstOrFail(),
            $amount,
            'sale_return',
            $return->uuid,
            "Return {$return->return_no} of invoice {$sale->invoice_no}",
            $userId,
        );
    }

    /**
     * Pick the refund method. Nothing chosen: pay back the way the customer paid. A credit sale was
     * not "paid" by any real method, so its default is the customer's account.
     */
    private function resolveMethod(Sale $sale, ?string $chosen): string
    {
        $method = $chosen ?: $sale->payment_method;

        if ($method === 'exchange' || $method === self::REFUND_LEDGER) {
            return $method;
        }

        if (! array_key_exists($method, Sale::PAYMENT_METHODS)) {
            return $sale->customer_id !== null ? self::REFUND_LEDGER : 'cash';
        }

        return $method;
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