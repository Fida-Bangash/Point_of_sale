<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\HeldBill;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Step 3: selling on credit (udhaar) from the POS checkout.
 */
class CreditSaleTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'cashier'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function product(array $over = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Food'], ['slug' => 'food', 'is_active' => true]);
        $unit     = Unit::query()->first()
            ?? Unit::forceCreate(['name' => 'Piece', 'short_name' => 'pc', 'is_active' => true]);

        return Product::create(array_merge([
            'name'           => 'Biscuit',
            'barcode'        => (string) random_int(100000, 999999),
            'category_id'    => $category->id,
            'unit_id'        => $unit->id,
            'purchase_price' => 60,
            'selling_price'  => 100,
            'quantity'       => 50,
            'alert_quantity' => 2,
            'is_active'      => true,
        ], $over));
    }

    private function customer(array $over = [], float $opening = 0): Customer
    {
        $customer = Customer::create($over + [
            'name'      => 'Ali Khan',
            'phone'     => '03001234567',
            'is_active' => true,
        ]);

        app(LedgerService::class)->openingBalance($customer, $opening);

        return $customer;
    }

    /** Checkout of $qty pieces (100 each) for the given customer. */
    private function checkout(User $user, Product $product, int $qty, array $payload = [])
    {
        return $this->actingAs($user)->postJson('/pos/checkout', array_merge([
            'items'          => [['product_id' => $product->id, 'quantity' => $qty]],
            'payment_method' => 'cash',
        ], $payload));
    }

    public function test_part_payment_puts_the_rest_on_the_customers_account(): void
    {
        $cashier  = $this->user();
        $product  = $this->product();
        $customer = $this->customer(opening: 1500);

        $this->checkout($cashier, $product, 5, [   // total 500
            'customer_id' => $customer->id,
            'paid'        => 200,
        ])->assertOk();

        $sale = Sale::firstOrFail();
        $this->assertSame($customer->id, $sale->customer_id);
        $this->assertSame('Ali Khan', $sale->customer_name);
        $this->assertEquals(500, $sale->total);
        $this->assertEquals(200, $sale->paid);
        $this->assertEquals(300, $sale->credit_amount);
        $this->assertEquals(0, $sale->change_amount);
        $this->assertEquals(1500, $sale->previous_balance);
        $this->assertSame(1800.0, $sale->balanceAfter());

        $entry = CustomerLedger::where('type', 'sale_credit')->firstOrFail();
        $this->assertEquals(300, $entry->amount);
        $this->assertSame('sale', $entry->ref_type);
        $this->assertSame($sale->uuid, $entry->ref_uuid);
        $this->assertSame($cashier->id, $entry->user_id);

        $this->assertSame(1800.0, $customer->currentBalance());
        $this->assertEquals(45, (float) $product->fresh()->quantity);
    }

    public function test_credit_method_with_nothing_paid_puts_the_whole_bill_on_credit(): void
    {
        $customer = $this->customer();

        $this->checkout($this->user(), $this->product(), 2, [
            'customer_id'    => $customer->id,
            'payment_method' => 'credit',
            'paid'           => 0,
        ])->assertOk();

        $sale = Sale::firstOrFail();
        $this->assertEquals(0, $sale->paid);
        $this->assertEquals(200, $sale->credit_amount);
        $this->assertSame('Credit', $sale->paymentLabel());
        $this->assertSame(200.0, $customer->currentBalance());
    }

    public function test_paid_less_than_total_needs_a_registered_customer(): void
    {
        $product = $this->product();

        $this->checkout($this->user(), $product, 2, ['paid' => 50])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['paid']);

        $this->checkout($this->user(), $product, 2, ['paid' => 0, 'payment_method' => 'credit'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);

        $this->assertSame(0, Sale::count());
        $this->assertSame(0, CustomerLedger::count());
        $this->assertEquals(50, (float) $product->fresh()->quantity);
    }

    public function test_credit_method_is_refused_when_nothing_is_left_on_credit(): void
    {
        $customer = $this->customer();

        $this->checkout($this->user(), $this->product(), 1, [
            'customer_id'    => $customer->id,
            'payment_method' => 'credit',
            'paid'           => 100,
        ])->assertStatus(422)->assertJsonValidationErrors(['paid']);

        $this->assertSame(0, Sale::count());
    }

    public function test_inactive_customer_cannot_take_goods_on_credit_and_nothing_is_saved(): void
    {
        $product  = $this->product();
        $customer = $this->customer(['is_active' => false]);

        $this->checkout($this->user(), $product, 2, ['customer_id' => $customer->id, 'paid' => 0, 'payment_method' => 'credit'])
            ->assertStatus(422)->assertJsonValidationErrors(['customer_id']);

        $this->assertSame(0, Sale::count());
        $this->assertSame(0, CustomerLedger::where('type', 'sale_credit')->count());
        $this->assertEquals(50, (float) $product->fresh()->quantity);
    }

    public function test_a_fully_paid_sale_to_a_customer_writes_no_ledger_entry_but_remembers_the_balance(): void
    {
        $customer = $this->customer(opening: 700);

        $this->checkout($this->user(), $this->product(), 1, [
            'customer_id' => $customer->id,
            'paid'        => 150,        // 50 change
        ])->assertOk();

        $sale = Sale::firstOrFail();
        $this->assertEquals(0, $sale->credit_amount);
        $this->assertEquals(50, $sale->change_amount);
        $this->assertEquals(700, $sale->previous_balance);
        $this->assertSame(0, CustomerLedger::where('type', 'sale_credit')->count());
        $this->assertSame(700.0, $customer->currentBalance());
    }

    public function test_cashier_is_stopped_by_the_credit_limit_even_if_they_send_the_override_flag(): void
    {
        $product  = $this->product();
        $customer = $this->customer(['credit_limit' => 1000], opening: 800);

        $payload = ['customer_id' => $customer->id, 'paid' => 0, 'payment_method' => 'credit'];

        $this->checkout($this->user('cashier'), $product, 3, $payload)         // 800 + 300 > 1000
            ->assertStatus(422)->assertJsonValidationErrors(['credit_limit']);

        $this->checkout($this->user('cashier'), $product, 3, $payload + ['override_credit_limit' => true])
            ->assertStatus(422)->assertJsonValidationErrors(['credit_limit']);

        $this->assertSame(0, Sale::count());
        $this->assertSame(800.0, $customer->currentBalance());
        $this->assertEquals(50, (float) $product->fresh()->quantity);
    }

    public function test_manager_can_override_the_credit_limit_and_the_override_is_recorded(): void
    {
        $customer = $this->customer(['credit_limit' => 1000], opening: 800);
        $payload  = ['customer_id' => $customer->id, 'paid' => 0, 'payment_method' => 'credit'];
        $manager  = $this->user('manager');
        $product  = $this->product();

        // Without the flag even a manager is stopped, so the override is always a conscious step
        $this->checkout($manager, $product, 3, $payload)
            ->assertStatus(422)->assertJsonValidationErrors(['credit_limit']);

        $this->checkout($manager, $product, 3, $payload + ['override_credit_limit' => true])->assertOk();

        $this->assertSame(1100.0, $customer->currentBalance());
        $this->assertStringContainsString(
            'overridden',
            CustomerLedger::where('type', 'sale_credit')->firstOrFail()->note
        );
    }

    public function test_a_sale_exactly_at_the_limit_is_allowed_and_a_zero_limit_blocks_all_credit(): void
    {
        $product = $this->product();

        $atLimit = $this->customer(['credit_limit' => 500]);
        $this->checkout($this->user(), $product, 5, ['customer_id' => $atLimit->id, 'paid' => 0, 'payment_method' => 'credit'])
            ->assertOk();

        $noCredit = $this->customer(['phone' => '03111234567', 'name' => 'Bilal', 'credit_limit' => 0]);
        $this->checkout($this->user(), $product, 1, ['customer_id' => $noCredit->id, 'paid' => 0, 'payment_method' => 'credit'])
            ->assertStatus(422)->assertJsonValidationErrors(['credit_limit']);
    }

    public function test_receipt_prints_previous_balance_credit_and_new_balance_and_never_changes(): void
    {
        $cashier  = $this->user();
        $customer = $this->customer(opening: 1500);

        $this->checkout($cashier, $this->product(), 5, ['customer_id' => $customer->id, 'paid' => 200])->assertOk();
        $sale = Sale::firstOrFail();

        $show = fn () => $this->actingAs($cashier)->get(route('sales.receipt', $sale))->assertOk();

        $show()->assertSeeInOrder(['Previous balance', '1,500.00', 'Credit on this bill', '300.00', 'New balance', '1,800.00']);

        // The customer pays later; the old receipt must still show the balances of that day
        app(LedgerService::class)->receivePayment($customer, 1000, 'Payment', $cashier->id);
        $show()->assertSeeInOrder(['Previous balance', '1,500.00', 'New balance', '1,800.00']);
    }

    public function test_receipt_of_a_cash_sale_to_a_walk_in_has_no_account_block(): void
    {
        $cashier = $this->user();
        $this->checkout($cashier, $this->product(), 1, ['paid' => 100])->assertOk();

        $this->actingAs($cashier)->get(route('sales.receipt', Sale::firstOrFail()))
            ->assertOk()
            ->assertDontSee('Previous balance')
            ->assertDontSee('New balance');
    }

    public function test_sales_list_shows_part_paid_sales_as_cash_plus_credit(): void
    {
        $cashier  = $this->user();
        $customer = $this->customer();

        $this->checkout($cashier, $this->product(), 2, ['customer_id' => $customer->id, 'paid' => 50])->assertOk();

        $this->actingAs($cashier)->get('/sales-list')->assertOk()->assertSee('Cash + Credit');
    }

    public function test_held_bill_remembers_the_customer_and_resumes_with_the_current_balance(): void
    {
        $cashier  = $this->user();
        $product  = $this->product();
        $customer = $this->customer(opening: 400);

        $uuid = $this->actingAs($cashier)->postJson('/pos/held', [
            'items'       => [['product_id' => $product->id, 'quantity' => 1]],
            'customer_id' => $customer->id,
        ])->assertOk()->json('uuid');

        $this->assertSame($customer->id, HeldBill::firstOrFail()->customer_id);

        app(LedgerService::class)->receivePayment($customer, 100);   // balance changes while the bill waits

        $this->actingAs($cashier)->getJson("/pos/held/{$uuid}")
            ->assertOk()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('customer.balance', 300)
            ->assertJsonPath('customer_name', 'Ali Khan');
    }

    public function test_resuming_a_held_bill_warns_when_the_customer_was_deactivated_meanwhile(): void
    {
        $cashier  = $this->user();
        $customer = $this->customer();

        $uuid = $this->actingAs($cashier)->postJson('/pos/held', [
            'items'       => [['product_id' => $this->product()->id, 'quantity' => 1]],
            'customer_id' => $customer->id,
        ])->json('uuid');

        $customer->update(['is_active' => false]);

        $this->actingAs($cashier)->getJson("/pos/held/{$uuid}")
            ->assertOk()
            ->assertJsonPath('warnings.0', 'Customer "Ali Khan" is inactive. Credit sales are not allowed for this customer.');
    }

    public function test_checkout_completing_a_held_bill_removes_it(): void
    {
        $cashier  = $this->user();
        $product  = $this->product();
        $customer = $this->customer();

        $uuid = $this->actingAs($cashier)->postJson('/pos/held', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]], 'customer_id' => $customer->id,
        ])->json('uuid');

        $this->checkout($cashier, $product, 1, [
            'customer_id' => $customer->id, 'paid' => 0, 'payment_method' => 'credit', 'held_uuid' => $uuid,
        ])->assertOk();

        $this->assertSame(0, HeldBill::count());
        $this->assertSame(100.0, $customer->currentBalance());
    }

    public function test_customer_search_finds_active_customers_by_name_or_phone_with_their_balance(): void
    {
        $cashier = $this->user();
        $this->customer(opening: 250);
        $this->customer(['name' => 'Bilal', 'phone' => '03111234567']);
        $this->customer(['name' => 'Old Alina', 'phone' => '03221234567', 'is_active' => false]);

        $this->actingAs($cashier)->getJson('/pos/customers?q=ali')
            ->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Ali Khan')
            ->assertJsonPath('0.balance', 250);

        $this->actingAs($cashier)->getJson('/pos/customers?q=0311-123')->assertJsonCount(1)->assertJsonPath('0.name', 'Bilal');
        $this->actingAs($cashier)->getJson('/pos/customers')->assertJsonCount(2);   // inactive customers never appear
    }

    public function test_cashier_can_create_a_customer_from_the_checkout_but_not_with_a_bad_or_duplicate_phone(): void
    {
        $cashier = $this->user();

        $this->actingAs($cashier)->postJson('/pos/customers', ['name' => ' Sara ', 'phone' => '0321-1234567'])
            ->assertCreated()
            ->assertJsonPath('name', 'Sara')
            ->assertJsonPath('phone', '03211234567')
            ->assertJsonPath('balance', 0);

        $this->actingAs($cashier)->postJson('/pos/customers', ['name' => 'Other', 'phone' => '03211234567'])
            ->assertStatus(422)->assertJsonValidationErrors(['phone']);

        $this->actingAs($cashier)->postJson('/pos/customers', ['name' => 'Other', 'phone' => '12345'])
            ->assertStatus(422)->assertJsonValidationErrors(['phone']);

        $this->assertSame(1, Customer::count());
    }

    public function test_stock_role_cannot_use_the_checkout_customer_endpoints(): void
    {
        $stock = $this->user('stock');

        $this->actingAs($stock)->getJson('/pos/customers')->assertForbidden();
        $this->actingAs($stock)->postJson('/pos/customers', ['name' => 'X', 'phone' => '03001234567'])->assertForbidden();
    }

    public function test_credit_sale_and_its_ledger_entry_are_queued_for_sync_in_the_right_order(): void
    {
        $customer = $this->customer();

        $this->checkout($this->user(), $this->product(), 1, [
            'customer_id' => $customer->id, 'paid' => 0, 'payment_method' => 'credit',
        ])->assertOk();

        $sale = Sale::firstOrFail();
        $sync = $sale->toSync();
        $this->assertSame($customer->uuid, $sync['customer_uuid']);
        $this->assertEquals(100, $sync['credit_amount']);
        $this->assertEquals(0, $sync['previous_balance']);

        $this->assertDatabaseHas('sync_queue', ['table_name' => 'sales', 'uuid' => $sale->uuid]);
        $this->assertDatabaseHas('sync_queue', ['table_name' => 'customer_ledger']);

        $tables = array_keys(SyncService::MODELS);
        $this->assertLessThan(array_search('sales', $tables), array_search('customers', $tables));
        $this->assertLessThan(array_search('customer_ledger', $tables), array_search('sales', $tables));
        $this->assertLessThan(array_search('customer_ledger', $tables), array_search('users', $tables));
    }

    public function test_a_sale_restored_from_an_older_server_row_without_credit_columns_gets_zeroes(): void
    {
        $cashier = $this->user();
        $uuid    = (string) Str::uuid();

        Sale::applyFromServer([
            'uuid' => $uuid, 'invoice_no' => 'INV-20261116-0001', 'user_uuid' => $cashier->uuid,
            'customer_uuid' => null, 'customer_name' => null, 'subtotal' => '100.00', 'discount' => '0.00',
            'total' => '100.00', 'paid' => '100.00', 'change_amount' => '0.00', 'credit_amount' => null,
            'previous_balance' => null, 'exchange_credit' => null, 'payment_method' => 'cash',
            'status' => 'completed', 'note' => null, 'sold_at' => '2026-11-16 10:00:00',
            'created_at' => '2026-11-16 10:00:00', 'updated_at' => '2026-11-16 10:00:00',
        ]);

        $sale = Sale::where('uuid', $uuid)->firstOrFail();
        $this->assertEquals(0, $sale->credit_amount);
        $this->assertEquals(0, $sale->previous_balance);
    }

    public function test_exchange_and_return_payment_lists_do_not_offer_credit(): void
    {
        $this->assertArrayNotHasKey('credit', Sale::PAYMENT_METHODS);
        $this->assertArrayHasKey('credit', Sale::checkoutMethods());
    }
}
