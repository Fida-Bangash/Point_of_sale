<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class CustomerLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function customer(array $overrides = []): Customer
    {
        return Customer::create($overrides + [
            'name'      => 'Ali Khan',
            'phone'     => '03001234567',
            'is_active' => true,
        ]);
    }

    private function ledger(): LedgerService
    {
        return app(LedgerService::class);
    }

    public function test_admin_creates_customer_with_opening_balance_as_first_ledger_entry(): void
    {
        $this->actingAs($this->admin())
            ->post('/customers', [
                'name' => ' Ali Khan ', 'phone' => '0300-123 4567', 'is_active' => 1,
                'opening_balance' => '1500', 'credit_limit' => '5000',
            ])
            ->assertSessionHasNoErrors();

        $customer = Customer::firstOrFail();

        $this->assertSame('Ali Khan', $customer->name);
        $this->assertSame('03001234567', $customer->phone);
        $this->assertDatabaseHas('customer_ledger', [
            'customer_id' => $customer->id, 'type' => 'opening', 'amount' => 1500,
        ]);
        $this->assertSame(1500.0, $customer->currentBalance());

        foreach (['customers', 'customer_ledger'] as $table) {
            $this->assertDatabaseHas('sync_queue', ['table_name' => $table, 'action' => 'upsert']);
        }
    }

    public function test_zero_opening_balance_creates_no_ledger_entry(): void
    {
        $this->actingAs($this->admin())
            ->post('/customers', ['name' => 'Bilal', 'phone' => '03111234567', 'is_active' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('customer_ledger', 0);
    }

    public function test_phone_must_be_valid_and_unique(): void
    {
        $this->customer();

        $this->actingAs($this->admin())
            ->post('/customers', ['name' => 'Other', 'phone' => '03001234567', 'is_active' => 1])
            ->assertSessionHasErrors('phone');

        $this->actingAs($this->admin())
            ->post('/customers', ['name' => 'Other', 'phone' => '12345', 'is_active' => 1])
            ->assertSessionHasErrors('phone');
    }

    public function test_cashier_cannot_set_credit_limit(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->post('/customers', [
                'name' => 'Sara', 'phone' => '03211234567', 'is_active' => 1, 'credit_limit' => '99999',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull(Customer::where('phone', '03211234567')->value('credit_limit'));
    }

    public function test_update_never_changes_the_balance(): void
    {
        $customer = $this->customer();
        $this->ledger()->charge($customer, 800);

        $this->actingAs($this->admin())
            ->put("/customers/{$customer->id}", [
                'name' => 'Ali K.', 'phone' => '03001234567', 'is_active' => 1, 'opening_balance' => '99999',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(800.0, $customer->fresh()->currentBalance());
    }

    public function test_charge_and_payment_move_the_balance_with_the_right_sign(): void
    {
        $customer = $this->customer();

        $this->ledger()->charge($customer, 1000);
        $this->ledger()->receivePayment($customer, 400);

        $this->assertSame(600.0, $customer->currentBalance());
        $this->assertDatabaseHas('customer_ledger', ['type' => 'payment', 'amount' => -400]);
    }

    public function test_paying_more_than_owed_leaves_a_negative_balance(): void
    {
        $customer = $this->customer();

        $this->ledger()->charge($customer, 100);
        $this->ledger()->receivePayment($customer, 250);

        $this->assertSame(-150.0, $customer->currentBalance());
    }

    public function test_amounts_are_rounded_to_two_decimals(): void
    {
        $customer = $this->customer();

        $this->ledger()->charge($customer, 0.1);
        $this->ledger()->charge($customer, 0.2);

        $this->assertSame(0.3, $customer->currentBalance());
    }

    public function test_zero_and_negative_amounts_are_rejected(): void
    {
        $customer = $this->customer();

        foreach ([0, -5] as $bad) {
            try {
                $this->ledger()->receivePayment($customer, $bad);
                $this->fail('Expected a ValidationException.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('amount', $e->errors());
            }
        }

        $this->assertDatabaseCount('customer_ledger', 0);
    }

    public function test_inactive_customer_cannot_take_credit_but_can_pay(): void
    {
        $customer = $this->customer(['is_active' => false]);

        try {
            $this->ledger()->charge($customer, 100);
            $this->fail('Charging an inactive customer should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('customer_id', $e->errors());
        }

        $this->ledger()->receivePayment($customer, 50);
        $this->assertSame(-50.0, $customer->currentBalance());
    }

    public function test_adjustment_requires_a_reason(): void
    {
        $customer = $this->customer();

        $this->expectException(ValidationException::class);
        $this->ledger()->adjust($customer, 100, '   ');
    }

   public function test_reversal_restores_the_balance_and_keeps_the_original(): void
{
    $customer = $this->customer();
    $user     = $this->admin();

    $this->ledger()->charge($customer, 700);
    $payment = $this->ledger()->receivePayment($customer, 300);

    $reversal = $this->ledger()->reverse($payment, 'Wrong customer', $user->id);

    $this->assertSame(700.0, $customer->currentBalance());
    $this->assertSame(-300.0, (float) $payment->fresh()->getRawOriginal('amount'));
    $this->assertSame($payment->id, $reversal->reversal_of_id);
    $this->assertSame('reversal', $reversal->type);
    $this->assertSame($user->id, $reversal->user_id);
}

    public function test_an_entry_can_only_be_reversed_once_and_a_reversal_cannot_be_reversed(): void
    {
        $customer = $this->customer();
        $entry    = $this->ledger()->charge($customer, 500);
        $reversal = $this->ledger()->reverse($entry, 'Mistake');

        try {
            $this->ledger()->reverse($entry, 'Again');
            $this->fail('Second reversal should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('entry', $e->errors());
        }

        try {
            $this->ledger()->reverse($reversal, 'Undo the undo');
            $this->fail('Reversing a reversal should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('entry', $e->errors());
        }

        $this->assertSame(0.0, $customer->currentBalance());
    }

    public function test_database_blocks_a_duplicate_reversal_even_if_the_service_is_bypassed(): void
    {
        $customer = $this->customer();
        $entry    = $this->ledger()->charge($customer, 500);
        $this->ledger()->reverse($entry, 'Mistake');

        $this->expectException(QueryException::class);

        CustomerLedger::create([
            'customer_id' => $customer->id, 'type' => 'reversal', 'amount' => -500,
            'reversal_of_id' => $entry->id, 'entry_date' => now(),
        ]);
    }

    public function test_ledger_entries_cannot_be_edited_or_deleted(): void
    {
        $customer = $this->customer();
        $entry    = $this->ledger()->charge($customer, 100);

        try {
            $entry->update(['amount' => 1]);
            $this->fail('Update should be blocked.');
        } catch (LogicException) {
            $this->assertSame(100.0, (float) $entry->fresh()->getRawOriginal('amount'));
        }

        $this->expectException(LogicException::class);
        $entry->delete();
    }

    public function test_ledger_page_shows_the_running_balance(): void
    {
        $customer = $this->customer();
        $this->ledger()->charge($customer, 1000);
        $this->ledger()->receivePayment($customer, 250);

        $this->actingAs($this->admin())
            ->get("/customers/{$customer->id}/ledger")
            ->assertOk()
            ->assertSee('750.00');
    }
}
