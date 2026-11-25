<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\User;
use App\Services\CustomerReportService;
use App\Services\LedgerService;
use App\Support\Cents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Step 6: statements, credit reports, exports and permissions.
 */
class CustomerStatementReportTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function customer(string $phone = '03001234567', string $name = 'Ali Khan'): Customer
    {
        return Customer::create(['name' => $name, 'phone' => $phone, 'is_active' => true]);
    }

    /** Write a ledger row on a given date through the real service, then move its date. */
    private function entry(Customer $c, string $kind, float $amount, string $date): CustomerLedger
    {
        $ledger = app(LedgerService::class);

        $entry = match ($kind) {
            'credit'  => $ledger->charge($c, $amount),
            'payment' => $ledger->receivePayment($c, $amount, method: 'cash'),
            'opening' => $ledger->openingBalance($c, $amount),
        };

        // entry_date is not editable through the model (ledger rows are append-only), so set it directly
        CustomerLedger::query()->whereKey($entry->id)->toBase()->update(['entry_date' => Carbon::parse($date)->toDateTimeString()]);

        return $entry->fresh();
    }

    public function test_cents_helper_never_loses_a_paisa(): void
    {
        $this->assertSame(10, Cents::from('0.10'));
        $this->assertSame(30, Cents::from('0.10') + Cents::from('0.20'));
        $this->assertSame(-60050, Cents::from('-600.50'));
        $this->assertSame(123457, Cents::from('1234.565'));
        $this->assertSame('-600.05', Cents::plain(-60005));
        $this->assertSame('1,234.56', Cents::format(123456));
    }

    public function test_statement_has_opening_balance_and_running_balance(): void
    {
        $c = $this->customer();
        $this->entry($c, 'credit', 500, '2026-01-05 10:00');
        $this->entry($c, 'payment', 200, '2026-01-10 10:00');
        $this->entry($c, 'credit', 100.10, '2026-02-01 09:00');
        $this->entry($c, 'credit', 0.20, '2026-02-01 09:00');   // same timestamp: id breaks the tie

        $s = app(CustomerReportService::class)->statement($c, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'));

        $this->assertSame(30000, $s['opening']);                      // 500 - 200
        $this->assertSame([40010, 40030], array_column($s['rows'], 'balance'));
        $this->assertSame(10030, $s['debit_total']);
        $this->assertSame(40030, $s['closing']);
    }

    public function test_statement_pages_and_csv_work_for_cashier(): void
    {
        $c = $this->customer();
        $this->entry($c, 'credit', 500, '2026-01-05 10:00');
        $cashier = $this->user('cashier');

        $this->actingAs($cashier)->get("/customers/{$c->id}/statement")->assertOk()->assertSee('500.00');
        $this->actingAs($cashier)->get("/customers/{$c->id}/statement/print")->assertOk();

        $csv = $this->actingAs($cashier)->get("/customers/{$c->id}/statement/csv?from=2026-01-01&to=2026-01-31");
        $csv->assertOk();
        $this->assertStringContainsString('500.00', $csv->streamedContent());
        $this->assertStringContainsString('Debit (udhaar)', $csv->streamedContent());
    }

    public function test_bad_date_range_is_rejected(): void
    {
        $c = $this->customer();

        $this->actingAs($this->user('cashier'))
            ->get("/customers/{$c->id}/statement?from=2026-02-10&to=2026-02-01")
            ->assertSessionHasErrors('to');
    }

    public function test_aging_puts_the_unpaid_part_in_the_newest_debts(): void
    {
        $c = $this->customer();
        $this->entry($c, 'credit', 1000, '2026-05-01 10:00');    // 100 days old
        $this->entry($c, 'credit', 400, '2026-07-10 10:00');     // 30 days old (as of 2026-08-09)
        $this->entry($c, 'payment', 1100, '2026-07-20 10:00');   // clears the old debt first, then 100 of the next

        $r = app(CustomerReportService::class)->aging(Carbon::parse('2026-08-09'));

        $this->assertCount(1, $r['rows']);
        $this->assertSame(30000, $r['rows'][0]['balance']);
        $this->assertSame(30000, $r['rows'][0]['buckets']['0-30']);
        $this->assertSame(0, $r['rows'][0]['buckets']['90+']);
    }

    public function test_aging_ignores_customers_who_do_not_owe(): void
    {
        $c = $this->customer();
        $this->entry($c, 'credit', 100, '2026-05-01 10:00');
        $this->entry($c, 'payment', 100, '2026-05-02 10:00');

        $this->assertSame([], app(CustomerReportService::class)->aging(Carbon::parse('2026-08-09'))['rows']);
    }

    public function test_collections_are_net_of_cancelled_payments(): void
    {
        $c = $this->customer();
        $admin = $this->user('admin');
        $ledger = app(LedgerService::class);

        $ledger->charge($c, 1000);
        $ledger->receivePayment($c, 300, method: 'cash');
        $bad = $ledger->receivePayment($c, 200, method: 'bank');
        $ledger->reverse($bad, 'wrong customer', $admin->id);

        $r = app(CustomerReportService::class)->collections(now(), now());

        $this->assertSame(50000, $r['collected']);
        $this->assertSame(20000, $r['cancelled']);
        $this->assertSame(30000, $r['net']);
        $this->assertSame(30000, $r['by_method']['cash']);
        $this->assertSame(0, $r['by_method']['bank']);
    }

    public function test_market_credit_lists_biggest_debtor_first(): void
    {
        $small = $this->customer('03001111111', 'Small');
        $big   = $this->customer('03002222222', 'Big');
        $adv   = $this->customer('03003333333', 'Advance');
        $this->entry($small, 'credit', 100, '2026-05-01 10:00');
        $this->entry($big, 'credit', 900, '2026-05-01 10:00');
        $this->entry($adv, 'opening', -50, '2026-05-01 10:00');

        $r = app(CustomerReportService::class)->marketCredit();

        $this->assertSame(100000, $r['receivable']);
        $this->assertSame(5000, $r['advances']);
        $this->assertSame(['Big', 'Small'], array_map(fn ($x) => $x['customer']->name, $r['rows']));
    }

    public function test_cashier_cannot_open_shop_wide_reports_or_exports(): void
    {
        $cashier = $this->user('cashier');

        foreach (['/customer-report', '/customer-report/aging', '/customer-report/collections',
                  '/customer-report/debtors.csv', '/customer-report/aging.csv', '/customer-report/collections.csv'] as $url) {
            $this->actingAs($cashier)->get($url)->assertForbidden();
        }
    }

    public function test_manager_and_admin_can_open_reports_and_exports(): void
    {
        foreach (['manager', 'admin'] as $role) {
            $u = $this->user($role);

            foreach (['/customer-report', '/customer-report/aging', '/customer-report/collections'] as $url) {
                $this->actingAs($u)->get($url)->assertOk();
            }

            foreach (['debtors', 'aging', 'collections'] as $name) {
                $this->actingAs($u)->get("/customer-report/{$name}.csv")->assertOk();
            }
        }
    }

    public function test_cashier_cannot_adjust_a_balance_but_manager_can(): void
    {
        $c = $this->customer();
        $payload = ['direction' => 'increase', 'amount' => '250.50', 'reason' => 'Old khata mistake'];

        $this->actingAs($this->user('cashier'))->post("/customers/{$c->id}/adjustments", $payload)->assertForbidden();
        $this->assertSame(0, CustomerLedger::count());

        $this->actingAs($this->user('manager'))->post("/customers/{$c->id}/adjustments", $payload)->assertRedirect();
        $this->assertSame(250.5, $c->fresh()->currentBalance());

        $this->actingAs($this->user('manager'))->post("/customers/{$c->id}/adjustments", ['direction' => 'decrease'] + $payload)->assertRedirect();
        $this->assertSame(0.0, $c->fresh()->currentBalance());
    }

    public function test_adjustment_needs_a_reason_and_a_valid_amount(): void
    {
        $c = $this->customer();
        $m = $this->user('manager');

        $this->actingAs($m)->post("/customers/{$c->id}/adjustments", ['direction' => 'increase', 'amount' => '10', 'reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->actingAs($m)->post("/customers/{$c->id}/adjustments", ['direction' => 'increase', 'amount' => '10.555', 'reason' => 'abc'])
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, CustomerLedger::count());
    }

    public function test_cashier_cannot_change_a_credit_limit(): void
    {
        $c = $this->customer();

        $this->actingAs($this->user('cashier'))->put("/customers/{$c->id}", [
            'name' => 'Ali Khan', 'phone' => '03001234567', 'is_active' => 1, 'credit_limit' => 99999,
        ]);

        $this->assertNull($c->fresh()->credit_limit);
    }

    public function test_csv_cells_cannot_run_as_formulas(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", \App\Support\CsvResponse::cell('=HYPERLINK("x")'));
        $this->assertSame('-600.00', \App\Support\CsvResponse::cell('-600.00'));
    }

    public function test_customer_list_is_sorted_by_highest_balance_first(): void
    {
        $small = $this->customer('03001111111', 'Aaa');
        $big   = $this->customer('03002222222', 'Zzz');
        $this->entry($small, 'credit', 100, '2026-05-01 10:00');
        $this->entry($big, 'credit', 900, '2026-05-01 10:00');

        $this->actingAs($this->user('cashier'))->get('/customers')->assertOk()->assertSeeInOrder(['Zzz', 'Aaa']);
    }
}
