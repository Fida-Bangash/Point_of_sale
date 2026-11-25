<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Support\Cents;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Read-only reports on top of customer_ledger. Nothing here writes anything.
 *
 * Money is always handled as whole paisa (int) through App\Support\Cents, never as floats.
 * Ordering is always (entry_date, id), so entries with the same timestamp keep a stable order and
 * the running balance is the same on every screen, export and reprint.
 */
class CustomerReportService
{
    /** Age buckets of the aging report: label => [min days, max days (null = no limit)] */
    public const BUCKETS = [
        '0-30'  => [0, 30],
        '31-60' => [31, 60],
        '61-90' => [61, 90],
        '90+'   => [91, null],
    ];

    /**
     * Statement of one customer for a date range (both ends optional = whole history).
     *
     * @return array{
     *   opening: int, rows: array<int, array{entry: CustomerLedger, debit: int, credit: int, balance: int}>,
     *   debit_total: int, credit_total: int, closing: int, reversed_ids: array<int, int>
     * }
     */
    public function statement(Customer $customer, ?CarbonInterface $from, ?CarbonInterface $to): array
    {
        $base = CustomerLedger::query()->where('customer_id', $customer->id);

        // Everything before the range is folded into one opening balance
        $opening = 0;

        if ($from) {
            foreach ((clone $base)->where('entry_date', '<', $from->copy()->startOfDay())->pluck('amount') as $amount) {
                $opening += Cents::from($amount);
            }
        }

        $entries = (clone $base)
            ->with('user:id,name')
            ->when($from, fn ($q) => $q->where('entry_date', '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where('entry_date', '<=', $to->copy()->endOfDay()))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $running = $opening;
        $debits  = 0;
        $credits = 0;
        $rows    = [];

        foreach ($entries as $entry) {
            $cents    = Cents::from($entry->amount);
            $running += $cents;
            $debit    = max($cents, 0);
            $credit   = max(-$cents, 0);
            $debits  += $debit;
            $credits += $credit;

            $rows[] = ['entry' => $entry, 'debit' => $debit, 'credit' => $credit, 'balance' => $running];
        }

        return [
            'opening'      => $opening,
            'rows'         => $rows,
            'debit_total'  => $debits,
            'credit_total' => $credits,
            'closing'      => $running,
            'reversed_ids' => (clone $base)->whereNotNull('reversal_of_id')->pluck('reversal_of_id')->map(fn ($id) => (int) $id)->all(),
        ];
    }

    /**
     * Outstanding debt per customer, split by age, as of the end of $asOf.
     *
     * The ledger has no invoice-by-invoice matching, so payments are assumed to clear the OLDEST debt
     * first (FIFO). What is still owed is therefore made of the NEWEST debit entries: walk the debits
     * from newest to oldest and give each one as much of the balance as it can hold.
     * Customers whose balance is zero or an advance are not in the report.
     *
     * @return array{rows: array<int, array<string, mixed>>, totals: array<string, int>, as_of: CarbonInterface}
     */
    public function aging(CarbonInterface $asOf): array
    {
        $asOfDay = $asOf->copy()->startOfDay();

        $perCustomer = [];

        foreach ($this->ledgerRows($asOf->copy()->endOfDay()) as $row) {
            $perCustomer[$row->customer_id][] = $row;
        }

        $names = Customer::query()->whereIn('id', array_keys($perCustomer))->get(['id', 'name', 'phone', 'credit_limit'])->keyBy('id');

        $rows   = [];
        $totals = array_fill_keys(array_keys(self::BUCKETS), 0) + ['balance' => 0];

        foreach ($perCustomer as $customerId => $entries) {
            $balance = array_sum(array_map(fn ($e) => Cents::from($e->amount), $entries));

            if ($balance <= 0 || ! $names->has($customerId)) {
                continue;
            }

            $buckets   = array_fill_keys(array_keys(self::BUCKETS), 0);
            $remaining = $balance;
            $oldest    = 0;

            foreach (array_reverse($entries) as $e) {   // newest first
                $cents = Cents::from($e->amount);

                if ($cents <= 0 || $remaining <= 0) {
                    continue;
                }

                $part      = min($cents, $remaining);
                $remaining -= $part;
                $age       = max(0, (int) Carbon::parse($e->entry_date)->startOfDay()->diffInDays($asOfDay));
                $buckets[$this->bucketFor($age)] += $part;
                $oldest    = max($oldest, $age);
            }

            // Not enough debit entries to explain the balance should be impossible; keep the money visible
            if ($remaining > 0) {
                $buckets['90+'] += $remaining;
            }

            $customer = $names->get($customerId);

            $rows[] = [
                'customer' => $customer,
                'balance'  => $balance,
                'buckets'  => $buckets,
                'oldest'   => $oldest,
            ];

            $totals['balance'] += $balance;
            foreach ($buckets as $label => $cents) {
                $totals[$label] += $cents;
            }
        }

        usort($rows, fn ($a, $b) => [$b['balance'], $a['customer']->name] <=> [$a['balance'], $b['customer']->name]);

        return ['rows' => $rows, 'totals' => $totals, 'as_of' => $asOf];
    }

    /**
     * Payments received between two dates. A cancelled payment shows up as its own negative line on
     * the day it was cancelled, so the net figure is what is really in the till.
     *
     * @return array{
     *   lines: Collection<int, array<string, mixed>>, collected: int, cancelled: int, net: int,
     *   payments: int, customers: int, by_method: array<string, int>
     * }
     */
    public function collections(CarbonInterface $from, CarbonInterface $to): array
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $payments = CustomerLedger::query()
            ->with(['customer:id,name,phone', 'user:id,name'])
            ->where('type', CustomerLedger::TYPE_PAYMENT)
            ->whereBetween('entry_date', [$start, $end])
            ->get();

        $reversals = CustomerLedger::query()
            ->with(['customer:id,name,phone', 'user:id,name', 'reversalOf'])
            ->where('type', CustomerLedger::TYPE_REVERSAL)
            ->whereBetween('entry_date', [$start, $end])
            ->whereHas('reversalOf', fn ($q) => $q->where('type', CustomerLedger::TYPE_PAYMENT))
            ->get();

        $lines     = collect();
        $collected = 0;
        $cancelled = 0;
        $byMethod  = [];

        foreach ($payments as $p) {
            $cents      = abs(Cents::from($p->amount));
            $collected += $cents;
            $method     = $p->payment_method ?: 'other';
            $byMethod[$method] = ($byMethod[$method] ?? 0) + $cents;

            $lines->push(['entry' => $p, 'cents' => $cents, 'kind' => 'payment', 'method' => $method, 'receipt' => $p->receipt_no]);
        }

        foreach ($reversals as $r) {
            $cents      = abs(Cents::from($r->amount));
            $cancelled += $cents;
            $method     = $r->reversalOf?->payment_method ?: 'other';
            $byMethod[$method] = ($byMethod[$method] ?? 0) - $cents;

            $lines->push(['entry' => $r, 'cents' => -$cents, 'kind' => 'cancelled', 'method' => $method, 'receipt' => $r->reversalOf?->receipt_no]);
        }

        return [
            'lines'     => $lines->sortBy(fn ($l) => [$l['entry']->entry_date->getTimestamp(), $l['entry']->id])->values(),
            'collected' => $collected,
            'cancelled' => $cancelled,
            'net'       => $collected - $cancelled,
            'payments'  => $payments->count(),
            'customers' => $payments->pluck('customer_id')->unique()->count(),
            'by_method' => $byMethod,
        ];
    }

    /**
     * Total credit in the market and the customers who owe the most (highest first).
     *
     * @return array{
     *   receivable: int, advances: int, net: int, debtors: int, customers: int, over_limit: int,
     *   rows: array<int, array<string, mixed>>
     * }
     */
    public function marketCredit(): array
    {
        $balances = [];
        $lastPaid = [];

        foreach ($this->ledgerRows(null) as $row) {
            $balances[$row->customer_id] = ($balances[$row->customer_id] ?? 0) + Cents::from($row->amount);

            if ($row->type === CustomerLedger::TYPE_PAYMENT) {
                $lastPaid[$row->customer_id] = $row->entry_date;   // rows arrive oldest first
            }
        }

        $customers = Customer::query()->get(['id', 'name', 'phone', 'credit_limit', 'is_active'])->keyBy('id');

        $receivable = 0;
        $advances   = 0;
        $overLimit  = 0;
        $rows       = [];

        foreach ($balances as $customerId => $balance) {
            $customer = $customers->get($customerId);

            if (! $customer) {
                continue;
            }

            if ($balance > 0) {
                $receivable += $balance;
                $limit       = $customer->credit_limit === null ? null : Cents::from($customer->credit_limit);
                $isOver      = $limit !== null && $balance > $limit;
                $overLimit  += $isOver ? 1 : 0;

                $rows[] = [
                    'customer'  => $customer,
                    'balance'   => $balance,
                    'limit'     => $limit,
                    'over'      => $isOver,
                    'last_paid' => isset($lastPaid[$customerId]) ? Carbon::parse($lastPaid[$customerId]) : null,
                ];
            } elseif ($balance < 0) {
                $advances += -$balance;
            }
        }

        usort($rows, fn ($a, $b) => [$b['balance'], $a['customer']->name] <=> [$a['balance'], $b['customer']->name]);

        return [
            'receivable' => $receivable,
            'advances'   => $advances,
            'net'        => $receivable - $advances,
            'debtors'    => count($rows),
            'customers'  => $customers->count(),
            'over_limit' => $overLimit,
            'rows'       => $rows,
        ];
    }

    private function bucketFor(int $days): string
    {
        foreach (self::BUCKETS as $label => [$min, $max]) {
            if ($days >= $min && ($max === null || $days <= $max)) {
                return $label;
            }
        }

        return '90+';
    }

    /**
     * Plain ledger rows (no models, so no casts), oldest first.
     *
     * @return \Illuminate\Support\LazyCollection<int, object>
     */
    private function ledgerRows(?CarbonInterface $until)
    {
        return CustomerLedger::query()
            ->toBase()
            ->select(['id', 'customer_id', 'type', 'amount', 'entry_date'])
            ->when($until, fn ($q) => $q->where('entry_date', '<=', $until->toDateTimeString()))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->cursor();
    }
}
