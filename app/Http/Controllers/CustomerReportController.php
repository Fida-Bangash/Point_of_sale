<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use App\Services\CustomerReportService;
use App\Support\Cents;
use App\Support\CsvResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Credit reports for the whole shop: market credit with the biggest debtors, aging, collections.
 * Admin and manager only (enforced in routes/web.php).
 */
class CustomerReportController extends Controller
{
    /** Total outstanding market credit + customers with the highest debt. */
    public function overview(Request $request, CustomerReportService $reports)
    {
        $data = $this->market($request, $reports);

        return view('pages.customer-report', $data + ['shop' => ShopSetting::current()]);
    }

    public function debtorsCsv(Request $request, CustomerReportService $reports)
    {
        $data = $this->market($request, $reports);

        $rows = (function () use ($data) {
            $rank = 0;

            foreach ($data['rows'] as $r) {
                yield [
                    ++$rank,
                    $r['customer']->name,
                    $r['customer']->phone,
                    Cents::plain($r['balance']),
                    $r['limit'] === null ? '' : Cents::plain($r['limit']),
                    $r['over'] ? 'Yes' : 'No',
                    $r['last_paid']?->format('Y-m-d') ?? '',
                ];
            }
        })();

        return CsvResponse::download(
            'outstanding-credit-' . now()->format('Ymd'),
            ['Rank', 'Customer', 'Phone', 'Outstanding balance', 'Credit limit', 'Over limit', 'Last payment'],
            $rows,
        );
    }

    public function aging(Request $request, CustomerReportService $reports)
    {
        $asOf = $this->date($request, 'as_of') ?? now();

        return view('pages.customer-aging', $reports->aging($asOf) + ['shop' => ShopSetting::current()]);
    }

    public function agingCsv(Request $request, CustomerReportService $reports)
    {
        $asOf = $this->date($request, 'as_of') ?? now();
        $data = $reports->aging($asOf);

        $rows = (function () use ($data) {
            foreach ($data['rows'] as $r) {
                yield array_merge(
                    [$r['customer']->name, $r['customer']->phone],
                    array_map([Cents::class, 'plain'], array_values($r['buckets'])),
                    [Cents::plain($r['balance']), $r['oldest']],
                );
            }

            yield array_merge(
                ['TOTAL', ''],
                array_map([Cents::class, 'plain'], array_map(fn ($l) => $data['totals'][$l], array_keys(CustomerReportService::BUCKETS))),
                [Cents::plain($data['totals']['balance']), ''],
            );
        })();

        return CsvResponse::download(
            'debt-aging-' . $asOf->format('Ymd'),
            array_merge(['Customer', 'Phone'], array_map(fn ($l) => $l . ' days', array_keys(CustomerReportService::BUCKETS)), ['Total outstanding', 'Oldest debt (days)']),
            $rows,
        );
    }

    public function collections(Request $request, CustomerReportService $reports)
    {
        [$from, $to] = $this->period($request);

        return view('pages.customer-collections', $reports->collections($from, $to) + [
            'from' => $from, 'to' => $to, 'shop' => ShopSetting::current(),
        ]);
    }

    public function collectionsCsv(Request $request, CustomerReportService $reports)
    {
        [$from, $to] = $this->period($request);
        $data = $reports->collections($from, $to);

        $rows = (function () use ($data) {
            foreach ($data['lines'] as $l) {
                $e = $l['entry'];

                yield [
                    $e->entry_date->format('Y-m-d'),
                    $e->entry_date->format('H:i'),
                    $l['kind'] === 'payment' ? 'Payment' : 'Cancelled payment',
                    $l['receipt'],
                    $e->customer?->name,
                    $e->customer?->phone,
                    $l['method'],
                    Cents::plain($l['cents']),
                    $e->user?->name,
                ];
            }

            yield ['', '', 'NET COLLECTED', '', '', '', '', Cents::plain($data['net']), ''];
        })();

        return CsvResponse::download(
            'collections-' . $from->format('Ymd') . '-' . $to->format('Ymd'),
            ['Date', 'Time', 'Type', 'Receipt no', 'Customer', 'Phone', 'Method', 'Amount', 'Entered by'],
            $rows,
        );
    }

    private function market(Request $request, CustomerReportService $reports): array
    {
        $limit = $request->validate(['top' => ['nullable', 'integer', 'in:10,25,50,0']])['top'] ?? 10;

        $data         = $reports->marketCredit();
        $data['top']  = (int) $limit;                       // 0 = everybody
        $data['rows'] = $limit > 0 ? array_slice($data['rows'], 0, $limit) : $data['rows'];

        return $data;
    }

    /** Today by default. */
    private function period(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = filled($data['from'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['from']) : now();
        $to   = filled($data['to'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['to']) : $from->copy();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function date(Request $request, string $key): ?Carbon
    {
        $value = $request->validate([$key => ['nullable', 'date_format:Y-m-d']])[$key] ?? null;

        return filled($value) ? Carbon::createFromFormat('Y-m-d', $value)->endOfDay() : null;
    }
}
