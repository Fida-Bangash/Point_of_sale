<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * Step 7: the balance must stay correct through sale + payment + return + reversal.
 */
class CustomerBalanceFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(['name' => 'Food'], ['slug' => 'food', 'is_active' => true]);
        $unit     = Unit::query()->first()
            ?? Unit::forceCreate(['name' => 'Piece', 'short_name' => 'pc', 'is_active' => true]);

        return Product::create([
            'name' => 'Biscuit', 'barcode' => (string) random_int(100000, 999999),
            'category_id' => $category->id, 'unit_id' => $unit->id,
            'purchase_price' => 60, 'selling_price' => 100, 'quantity' => 50,
            'alert_quantity' => 2, 'is_active' => true,
        ]);
    }

    private function customer(): Customer
    {
        return Customer::create(['name' => 'Ali Khan', 'phone' => '03001234567', 'is_active' => true]);
    }

    /** 10 pieces = 1000, customer pays 400 now, 600 goes on his account. */
    private function creditSale(User $user, Product $product, Customer $customer): Sale
    {
        $this->actingAs($user)->postJson('/pos/checkout', [
            'items'          => [['product_id' => $product->id, 'quantity' => 10]],
            'payment_method' => 'cash',
            'paid'           => 400,
            'customer_id'    => $customer->id,
        ])->assertOk();

        return Sale::firstOrFail();
    }

    private function balance(Customer $customer): float
    {
        return $customer->fresh()->currentBalance();
    }

    public function test_balance_is_correct_after_sale_payment_and_return(): void
    {
        $user     = $this->user();
        $customer = $this->customer();
        $sale     = $this->creditSale($user, $this->product(), $customer);

        // 1) credit sale: 1000 - 400 paid = 600 owed
        $this->assertSame(600.0, $this->balance($customer));
        $this->assertEquals(0, $sale->fresh()->previous_balance);
        $this->assertEquals(600, $sale->fresh()->credit_amount);

        // 2) the customer pays 200 later
        app(LedgerService::class)->receivePayment($customer, 200, 'part payment', $user->id, null, 'cash');
        $this->assertSame(400.0, $this->balance($customer));

        // 3) he returns 3 pieces (300) and the refund goes to his account
        $item = $sale->items()->firstOrFail();
        $this->actingAs($user)->post("/sales/{$sale->id}/return", [
            'items'         => [['sale_item_id' => $item->id, 'quantity' => 3]],
            'refund_method' => 'ledger',
        ])->assertSessionHasNoErrors();

        $this->assertSame(100.0, $this->balance($customer));

        // The balance always equals the sum of the entries, in the order they were written.
        $entries = $customer->ledger()->orderBy('id')->get();
        $this->assertSame(['sale_credit', 'payment', 'return_credit'], $entries->pluck('type')->all());
        $this->assertSame([600.0, -200.0, -300.0], $entries->map(fn ($e) => (float) $e->amount)->all());
        $this->assertSame(100.0, round($entries->sum(fn ($e) => (float) $e->amount), 2));
    }

    public function test_cash_return_of_a_credit_sale_does_not_change_the_balance_below_what_was_paid(): void
    {
        $user     = $this->user();
        $customer = $this->customer();
        $sale     = $this->creditSale($user, $this->product(), $customer);

        $item = $sale->items()->firstOrFail();
        $this->actingAs($user)->post("/sales/{$sale->id}/return", [
            'items'         => [['sale_item_id' => $item->id, 'quantity' => 3]],
            'refund_method' => 'cash',   // 300 <= 400 really paid, so it is all paid out in cash
        ])->assertSessionHasNoErrors();

        $this->assertSame(600.0, $this->balance($customer));
    }

    public function test_reversing_a_payment_restores_the_balance_without_touching_the_original(): void
    {
        $user     = $this->user();
        $customer = $this->customer();
        $this->creditSale($user, $this->product(), $customer);

        $ledger  = app(LedgerService::class);
        $payment = $ledger->receivePayment($customer, 200, null, $user->id, null, 'cash');
        $this->assertSame(400.0, $this->balance($customer));

        $reversal = $ledger->reverse($payment, 'entered by mistake', $user->id);

        $this->assertSame(600.0, $this->balance($customer));
        $this->assertSame($payment->id, $reversal->reversal_of_id);
        $this->assertEquals(-200, $payment->fresh()->amount);   // original row untouched

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $ledger->reverse($payment, 'second time');               // cannot be reversed twice
    }

    public function test_ledger_entries_cannot_be_edited_or_deleted(): void
    {
        $customer = $this->customer();
        $entry    = app(LedgerService::class)->adjust($customer, 50, 'test', null);

        try {
            $entry->update(['amount' => 1]);
            $this->fail('Editing a ledger entry must throw.');
        } catch (LogicException) {
            // expected
        }

        try {
            $entry->delete();
            $this->fail('Deleting a ledger entry must throw.');
        } catch (LogicException) {
            // expected
        }

        $this->assertEquals(50, $entry->fresh()->amount);
    }

    public function test_a_pull_from_the_server_never_overwrites_an_existing_ledger_entry(): void
    {
        $customer = $this->customer();
        $entry    = app(LedgerService::class)->adjust($customer, 50, 'test', null);

        CustomerLedger::applyFromServer([
            'uuid'       => $entry->uuid,
            'type'       => 'adjustment',
            'amount'     => '9999.00',
            'entry_date' => now()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->assertEquals(50, $entry->fresh()->amount);
        $this->assertSame(50.0, $this->balance($customer));
    }

    public function test_every_ledger_entry_is_queued_for_sync_after_the_customer(): void
    {
        $user     = $this->user();
        $customer = $this->customer();
        $this->creditSale($user, $this->product(), $customer);

        $this->assertDatabaseHas('sync_queue', ['table_name' => 'customers', 'action' => 'upsert']);
        $this->assertDatabaseHas('sync_queue', ['table_name' => 'customer_ledger', 'action' => 'upsert']);

        $order = array_keys(\App\Services\SyncService::MODELS);
        $this->assertLessThan(
            array_search('customer_ledger', $order, true),
            array_search('customers', $order, true),
        );
    }
}