<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Step 4: wasooli (receiving, receipt, cancelling customer payments).
 */
class CustomerPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'cashier'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** A customer who currently owes $owes. */
    private function customer(float $owes = 1000, array $over = []): Customer
    {
        $customer = Customer::create($over + [
            'name'      => 'Ali Khan',
            'phone'     => '03001234567',
            'is_active' => true,
        ]);

        app(LedgerService::class)->openingBalance($customer, $owes);

        return $customer;
    }

    private function pay(User $user, Customer $customer, array $payload = [])
    {
        return $this->actingAs($user)->post("/customers/{$customer->id}/payments", $payload + [
            'amount'         => 400,
            'payment_method' => 'cash',
        ]);
    }

    public function test_cashier_receives_a_payment_and_is_sent_to_the_receipt(): void
    {
        $customer = $this->customer(1000);

        $response = $this->pay($this->user('cashier'), $customer, [
            'amount' => '400', 'payment_method' => 'easypaisa', 'note' => 'Paid by wallet',
        ]);

        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.payments.receipt', [$customer, $entry]));

        $this->assertEquals(-400, $entry->amount);
        $this->assertSame('easypaisa', $entry->payment_method);
        $this->assertSame(1000.0, (float) $entry->previous_balance);
        $this->assertSame(600.0, $entry->balanceAfter());
        $this->assertMatchesRegularExpression('/^PAY-\d{8}-0001$/', $entry->receipt_no);
        $this->assertSame(600.0, $customer->currentBalance());
        $this->assertDatabaseHas('sync_queue', ['table_name' => 'customer_ledger', 'action' => 'upsert']);
    }

    public function test_receipt_numbers_count_up_within_a_day(): void
    {
        $customer = $this->customer(1000);
        $cashier  = $this->user();

        $this->pay($cashier, $customer, ['amount' => 100]);
        $this->pay($cashier, $customer, ['amount' => 100]);

        $numbers = CustomerLedger::where('type', 'payment')->orderBy('id')->pluck('receipt_no')->all();

        $this->assertStringEndsWith('-0001', $numbers[0]);
        $this->assertStringEndsWith('-0002', $numbers[1]);
    }

    public function test_paying_more_than_owed_needs_the_advance_to_be_confirmed(): void
    {
        $customer = $this->customer(300);

        $this->pay($this->user(), $customer, ['amount' => 500])->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('customer_ledger', ['type' => 'payment']);
        $this->assertSame(300.0, $customer->currentBalance());

        $this->pay($this->user(), $customer, ['amount' => 500, 'confirm_advance' => 1])
            ->assertSessionHasNoErrors();

        // 300 owed - 500 paid = 200 advance, shown as a negative balance
        $this->assertSame(-200.0, $customer->currentBalance());
    }

    public function test_a_customer_with_no_balance_needs_the_confirmation_for_any_payment(): void
    {
        $customer = $this->customer(0);

        $this->pay($this->user(), $customer, ['amount' => 50])->assertSessionHasErrors('amount');
        $this->pay($this->user(), $customer, ['amount' => 50, 'confirm_advance' => 1])->assertSessionHasNoErrors();

        $this->assertSame(-50.0, $customer->currentBalance());
    }

    public function test_exactly_the_balance_needs_no_confirmation(): void
    {
        $customer = $this->customer(250);

        $this->pay($this->user(), $customer, ['amount' => 250])->assertSessionHasNoErrors();

        $this->assertSame(0.0, $customer->currentBalance());
    }

    public function test_invalid_input_is_rejected_and_nothing_is_saved(): void
    {
        $customer = $this->customer(1000);
        $cashier  = $this->user();

        $this->pay($cashier, $customer, ['amount' => 0])->assertSessionHasErrors('amount');
        $this->pay($cashier, $customer, ['amount' => -5])->assertSessionHasErrors('amount');
        $this->pay($cashier, $customer, ['amount' => 'abc'])->assertSessionHasErrors('amount');
        $this->pay($cashier, $customer, ['payment_method' => 'card'])->assertSessionHasErrors('payment_method');
        $this->pay($cashier, $customer, ['payment_date' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('payment_date');

        $this->assertDatabaseMissing('customer_ledger', ['type' => 'payment']);
        $this->assertSame(1000.0, $customer->currentBalance());
    }

    public function test_a_back_dated_payment_keeps_its_date_and_an_inactive_customer_can_still_pay(): void
    {
        $customer = $this->customer(1000, ['is_active' => false]);
        $date     = now()->subDays(3)->toDateString();

        $this->pay($this->user(), $customer, ['amount' => 100, 'payment_date' => $date])
            ->assertSessionHasNoErrors();

        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        $this->assertSame($date, $entry->entry_date->toDateString());
        $this->assertSame(900.0, $customer->currentBalance());
    }

    public function test_receipt_shows_previous_and_new_balance_in_both_sizes(): void
    {
        $customer = $this->customer(1000);
        $this->pay($this->user(), $customer, ['amount' => 400]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        $url = route('customers.payments.receipt', [$customer, $entry]);

        $this->actingAs($this->user())->get($url)
            ->assertOk()
            ->assertSee($entry->receipt_no)
            ->assertSee('Previous balance')
            ->assertSee('1,000.00')
            ->assertSee('600.00')
            ->assertSee('size: 80mm', false)
            ->assertDontSee('CANCELLED');

        $this->actingAs($this->user())->get($url . '?size=a4')
            ->assertOk()
            ->assertSee('size: A4', false);
    }

    public function test_a_reprinted_receipt_never_changes_when_the_balance_moves_on(): void
    {
        $customer = $this->customer(1000);
        $this->pay($this->user(), $customer, ['amount' => 400]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        app(LedgerService::class)->charge($customer, 5000);   // balance is now 5600

        $this->actingAs($this->user())
            ->get(route('customers.payments.receipt', [$customer, $entry]))
            ->assertOk()
            ->assertSee('1,000.00')
            ->assertSee('600.00')
            ->assertDontSee('5,600.00');
    }

    public function test_an_advance_is_labelled_on_the_receipt(): void
    {
        $customer = $this->customer(100);
        $this->pay($this->user(), $customer, ['amount' => 300, 'confirm_advance' => 1]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        $this->actingAs($this->user())
            ->get(route('customers.payments.receipt', [$customer, $entry]))
            ->assertOk()
            ->assertSee('200.00 (advance)');
    }

    public function test_receipt_urls_of_other_customers_or_other_entry_types_are_not_found(): void
    {
        $ali  = $this->customer(1000);
        $sara = $this->customer(500, ['name' => 'Sara', 'phone' => '03111234567']);
        $this->pay($this->user(), $ali, ['amount' => 100]);

        $payment = CustomerLedger::where('type', 'payment')->firstOrFail();
        $opening = CustomerLedger::where('type', 'opening')->where('customer_id', $ali->id)->firstOrFail();
        $cashier = $this->user();

        $this->actingAs($cashier)->get(route('customers.payments.receipt', [$sara, $payment]))->assertNotFound();
        $this->actingAs($cashier)->get(route('customers.payments.receipt', [$ali, $opening]))->assertNotFound();
    }

    public function test_only_an_admin_can_cancel_a_payment(): void
    {
        $customer = $this->customer(1000);
        $this->pay($this->user(), $customer, ['amount' => 400]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();
        $url   = route('customers.payments.cancel', [$customer, $entry]);

        $this->actingAs($this->user('cashier'))->post($url, ['reason' => 'Mistake'])->assertForbidden();
        $this->actingAs($this->user('manager'))->post($url, ['reason' => 'Mistake'])->assertForbidden();

        $this->assertSame(600.0, $customer->currentBalance());
        $this->assertDatabaseMissing('customer_ledger', ['type' => 'reversal']);
    }

    public function test_admin_cancels_a_payment_with_a_reversal_entry(): void
    {
        $customer = $this->customer(1000);
        $admin    = $this->user('admin');
        $this->pay($admin, $customer, ['amount' => 400]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('customers.payments.cancel', [$customer, $entry]), ['reason' => 'Wrong customer'])
            ->assertSessionHasNoErrors();

        $reversal = CustomerLedger::where('type', 'reversal')->firstOrFail();

        $this->assertSame($entry->id, $reversal->reversal_of_id);
        $this->assertEquals(400, $reversal->amount);
        $this->assertStringContainsString('Wrong customer', $reversal->note);
        $this->assertSame($admin->id, $reversal->user_id);

        // The original payment is untouched and the balance is back where it was
        $this->assertEquals(-400, $entry->fresh()->amount);
        $this->assertSame(1000.0, $customer->currentBalance());

        // The receipt now says CANCELLED
        $this->actingAs($admin)
            ->get(route('customers.payments.receipt', [$customer, $entry]))
            ->assertOk()
            ->assertSee('CANCELLED');
    }

    public function test_a_payment_cannot_be_cancelled_twice_and_a_reason_is_required(): void
    {
        $customer = $this->customer(1000);
        $admin    = $this->user('admin');
        $this->pay($admin, $customer, ['amount' => 400]);
        $entry = CustomerLedger::where('type', 'payment')->firstOrFail();
        $url   = route('customers.payments.cancel', [$customer, $entry]);

        $this->actingAs($admin)->post($url, ['reason' => ''])->assertSessionHasErrors('reason');
        $this->assertDatabaseMissing('customer_ledger', ['type' => 'reversal']);

        $this->actingAs($admin)->post($url, ['reason' => 'Mistake'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post($url, ['reason' => 'Again'])->assertSessionHasErrors('entry');

        $this->assertSame(1, CustomerLedger::where('type', 'reversal')->count());
        $this->assertSame(1000.0, $customer->currentBalance());
    }

    public function test_only_payments_can_be_cancelled_here(): void
    {
        $customer = $this->customer(1000);
        $admin    = $this->user('admin');
        $opening  = CustomerLedger::where('type', 'opening')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('customers.payments.cancel', [$customer, $opening]), ['reason' => 'No'])
            ->assertNotFound();

        $this->assertSame(1000.0, $customer->currentBalance());
    }

    public function test_the_ledger_page_offers_the_payment_form_and_only_admins_see_cancel(): void
    {
        $customer = $this->customer(1000);
        $this->pay($this->user(), $customer, ['amount' => 400]);

        $this->actingAs($this->user('cashier'))->get("/customers/{$customer->id}/ledger")
            ->assertOk()
            ->assertSee('Receive payment')
            ->assertSee('PAY-')
            ->assertDontSee('data-cancel-payment', false)
            ->assertDontSee('id="cancel-payment"', false);

        $this->actingAs($this->user('admin'))->get("/customers/{$customer->id}/ledger")
            ->assertOk()
            ->assertSee('data-cancel-payment', false)
            ->assertSee('id="cancel-payment"', false);
    }

    public function test_payment_details_are_part_of_the_sync_row(): void
    {
        $customer = $this->customer(1000);
        $this->pay($this->user(), $customer, ['amount' => 400, 'payment_method' => 'bank']);

        $row = CustomerLedger::where('type', 'payment')->firstOrFail()->toSync();

        $this->assertSame('bank', $row['payment_method']);
        $this->assertNotNull($row['receipt_no']);
        $this->assertEquals(1000, $row['previous_balance']);
    }
}
