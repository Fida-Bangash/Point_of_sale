<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ShopSetting;
use App\Services\CustomerReportService;
use App\Support\Cents;
use App\Support\CsvResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Customer statement: every debit and credit with a running balance, for a date range.
 * Read-only, so cashiers may use it (the route group decides who gets in).
 */
class CustomerStatementController extends Controller
{
    public function show(Request $request, Customer $customer, CustomerReportService $reports)
    {
        [$from, $to] = $this->range($request);

        return view('pages.customer-statement', $this->data($customer, $from, $to, $reports) + [
            'canAdjust' => $request->user()->hasRole('admin', 'manager'),
        ]);
    }

    /** Clean page for printing, or for "Save as PDF" in the print dialog. */
    public function print(Request $request, Customer $customer, CustomerReportService $reports)
    {
        [$from, $to] = $this->range($request);

        return view('pages.customer-statement-print', $this->data($customer, $from, $to, $reports));
    }

    public function csv(Request $request, Customer $customer, CustomerReportService $reports)
    {
        [$from, $to] = $this->range($request);

        $s = $reports->statement($customer, $from, $to);

        $rows = (function () use ($s, $customer) {
            yield ['', 'Opening balance', '', '', '', '', '', Cents::plain($s['opening']), ''];

            foreach ($s['rows'] as $r) {
                $e = $r['entry'];

                yield [
                    $e->entry_date->format('Y-m-d'),
                    $e->entry_date->format('H:i'),
                    $e->typeLabel(),
                    $e->receipt_no,
                    $e->payment_method ? $e->methodLabel() : '',
                    $r['debit'] > 0 ? Cents::plain($r['debit']) : '',
                    $r['credit'] > 0 ? Cents::plain($r['credit']) : '',
                    Cents::plain($r['balance']),
                    trim(($e->note ?? '') . ($e->user ? ' [' . $e->user->name . ']' : '')),
                ];
            }

            yield ['', 'Total / Closing balance', '', '', '', Cents::plain($s['debit_total']), Cents::plain($s['credit_total']), Cents::plain($s['closing']), ''];
        })();

        $name = 'statement-' . $customer->name . '-' . ($from?->format('Ymd') ?? 'start') . '-' . ($to?->format('Ymd') ?? now()->format('Ymd'));

        return CsvResponse::download(
            $name,
            ['Date', 'Time', 'Type', 'Receipt no', 'Method', 'Debit (udhaar)', 'Credit (wasooli)', 'Balance', 'Note / entered by'],
            $rows,
        );
    }

    private function data(Customer $customer, ?Carbon $from, ?Carbon $to, CustomerReportService $reports): array
    {
        return [
            'customer'  => $customer,
            'statement' => $reports->statement($customer, $from, $to),
            'from'      => $from,
            'to'        => $to,
            'shop'      => ShopSetting::current(),
        ];
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return [
            filled($data['from'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['from'])->startOfDay() : null,
            filled($data['to'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['to'])->endOfDay() : null,
        ];
    }
}
