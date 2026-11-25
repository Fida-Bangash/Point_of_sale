<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Exchange;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Unit;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Step 5: returns and exchanges of credit (udhaar) sales.
 */
class CreditReturnTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'manager'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function product(array $over = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Food'], ['slug' => 'food', 'is_active' => true]);
        $unit     = Unit::query()->first()
            ?? Unit::forceCreate(['name' => 'Piece', 'short_name' => 'pc', 'is_active' => true]);

        return Product::create(array_merge([
            'name' => 'Biscuit', 'barcode' => (string) random_int(100000, 999999),
            'category_id' => $category->id, 'unit_id' => $unit->id,
            'purchase_price' => 60, 'selling_price' => 100, 'quantity' => 50,
            'alert_quantity' => 2, 'is_active' => true,
        ], $over));
    }

    private function customer(array $over = []): Customer
    {
        return Customer::create($over + ['name' => 'Ali Khan', 'phone' => '03001234567', 'is_active' => true]);
    }

    private function balance(Customer $c): float
    {
        return $c->fresh()->currentBalance();
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

    private function returnAll(User $user, Sale $sale, array $payload = [], int $qty = 10)
    {
        $item = $sale->items()->firstOrFail();

        return $this->actingAs($user)->post("/sales/{$sale->id}/return", array_merge([
            'items'         => [['sale_item_id' => $item->id, 'quantity' => $qty]],
            'refund_method' => 'cash',
        ], $payload));
    }

    public function test_full_cash_return_of_a_credit_sale_only_pays_out_what_was_paid(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);
        $this->assertSame(600.0, $this->balance($customer));

        $this->returnAll($user, $sale, ['refund_method' => 'cash'])->assertRedirect();

        $return = SaleReturn::firstOrFail();
        $this->assertSame(1000.0, (float) $return->refund_total);
        $this->assertSame(600.0, (float) $return->ledger_amount);   // the unpaid part clears the khata
        $this->assertSame(400.0, $return->cashAmount());            // only this was really paid
        $this->assertSame(0.0, $this->balance($customer));

        $entry = CustomerLedger::where('type', CustomerLedger::TYPE_RETURN)->firstOrFail();
        $this->assertSame(-600.0, (float) $entry->amount);
        $this->assertSame($return->uuid, $entry->ref_uuid);
        $this->assertSame(50.0, (float) $product->fresh()->quantity);
    }

    public function test_ledger_refund_credits_the_whole_amount_to_the_account(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);

        $this->returnAll($user, $sale, ['refund_method' => 'ledger'])->assertRedirect();

        $this->assertSame(1000.0, (float) SaleReturn::firstOrFail()->ledger_amount);
        $this->assertSame(-400.0, $this->balance($customer));       // 600 owed - 1000 credit = advance
    }

    public function test_partial_returns_never_pay_out_more_cash_than_was_paid(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);
        $item = $sale->items()->firstOrFail();

        foreach ([3, 3, 4] as $qty) {   // 300 + 300 + 400
            $this->actingAs($user)->post("/sales/{$sale->id}/return", [
                'items' => [['sale_item_id' => $item->id, 'quantity' => $qty]], 'refund_method' => 'cash',
            ])->assertRedirect();
        }

        $returns = SaleReturn::orderBy('id')->get();
        $this->assertSame([300.0, 300.0, 400.0], $returns->map(fn ($r) => (float) $r->refund_total)->all());
        $this->assertSame(400.0, round($returns->sum(fn ($r) => $r->cashAmount()), 2));
        $this->assertSame(600.0, round($returns->sum(fn ($r) => (float) $r->ledger_amount), 2));
        $this->assertSame(0.0, $this->balance($customer));
    }

    public function test_walk_in_sale_cannot_be_refunded_to_a_ledger(): void
    {
        $user = $this->user(); $product = $this->product();
        $this->actingAs($user)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]], 'payment_method' => 'cash', 'paid' => 200,
        ])->assertOk();

        $this->returnAll($user, Sale::firstOrFail(), ['refund_method' => 'ledger'], 2)->assertSessionHasErrors('refund_method');
        $this->assertSame(0, SaleReturn::count());
        $this->assertSame(0, CustomerLedger::count());
    }

    public function test_cash_return_of_a_fully_paid_sale_does_not_touch_the_ledger(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $this->actingAs($user)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]], 'payment_method' => 'cash',
            'paid' => 200, 'customer_id' => $customer->id,
        ])->assertOk();

        $this->returnAll($user, Sale::firstOrFail(), ['refund_method' => 'cash'], 2)->assertRedirect();

        $this->assertSame(0.0, (float) SaleReturn::firstOrFail()->ledger_amount);
        $this->assertSame(0, CustomerLedger::count());
    }

    public function test_exchange_with_less_payment_puts_the_rest_on_the_account(): void
    {
        $user = $this->user('manager'); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);              // balance 600
        $item = $sale->items()->firstOrFail();
        $other = $this->product(['name' => 'Juice', 'selling_price' => 500]);

        // return 2 (200 credit), take 1 juice (500): difference 300, customer pays 100, 200 on account
        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", [
            'return_items'   => [['sale_item_id' => $item->id, 'quantity' => 2]],
            'new_items'      => [['product_id' => $other->id, 'quantity' => 1]],
            'payment_method' => 'cash', 'paid' => 100,
        ])->assertRedirect();

        $exchange = Exchange::firstOrFail();
        $this->assertSame(100.0, (float) $exchange->cash_in);
        $this->assertSame(200.0, (float) $exchange->ledger_in);
        $this->assertSame(800.0, $this->balance($customer));               // 600 + 200
        $this->assertSame($customer->id, $exchange->newSale->customer_id);
    }

    public function test_walk_in_exchange_must_still_pay_in_full(): void
    {
        $user = $this->user(); $product = $this->product(); $other = $this->product(['name' => 'Juice', 'selling_price' => 500]);
        $this->actingAs($user)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]], 'payment_method' => 'cash', 'paid' => 200,
        ])->assertOk();
        $sale = Sale::firstOrFail(); $item = $sale->items()->firstOrFail();

        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", [
            'return_items' => [['sale_item_id' => $item->id, 'quantity' => 1]],
            'new_items' => [['product_id' => $other->id, 'quantity' => 1]],
            'payment_method' => 'cash', 'paid' => 100,
        ])->assertSessionHasErrors('paid');
        $this->assertSame(0, Exchange::count());
    }

    public function test_exchange_refund_can_be_credited_to_the_account(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);              // balance 600
        $item = $sale->items()->firstOrFail();
        $cheap = $this->product(['name' => 'Toffee', 'selling_price' => 50]);

        // return 4 (400), take 2 toffee (100): 100 credit applied, 300 refund
        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", [
            'return_items' => [['sale_item_id' => $item->id, 'quantity' => 4]],
            'new_items' => [['product_id' => $cheap->id, 'quantity' => 2]],
            'payment_method' => 'cash', 'refund_to' => 'ledger',
        ])->assertRedirect();

        $exchange = Exchange::firstOrFail();
        $this->assertSame(300.0, (float) $exchange->ledger_out);
        $this->assertSame(0.0, (float) $exchange->cash_out);
        $this->assertSame(300.0, $this->balance($customer));                // 600 - 300
        $this->assertSame(300.0, (float) SaleReturn::firstOrFail()->ledger_amount);
    }

    public function test_exchange_cash_refund_of_a_credit_sale_is_limited_to_what_was_paid(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);              // paid 400, owes 600
        $item = $sale->items()->firstOrFail();
        $cheap = $this->product(['name' => 'Toffee', 'selling_price' => 50]);

        // return everything (1000), take 6 toffee (300): 700 left. He only paid 400 in cash, 300 of that
        // went into the new goods, so 100 cash goes out and 600 clears his account.
        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", [
            'return_items' => [['sale_item_id' => $item->id, 'quantity' => 10]],
            'new_items' => [['product_id' => $cheap->id, 'quantity' => 6]],
            'payment_method' => 'cash', 'refund_to' => 'cash',
        ])->assertRedirect();

        $exchange = Exchange::firstOrFail();
        $this->assertSame(100.0, (float) $exchange->cash_out);
        $this->assertSame(600.0, (float) $exchange->ledger_out);
        $this->assertSame(0.0, $this->balance($customer));
    }

    public function test_exchange_over_the_credit_limit_needs_manager_override(): void
    {
        $user = $this->user(); $product = $this->product();
        $customer = $this->customer(['credit_limit' => 700]);
        $sale = $this->creditSale($user, $product, $customer);              // balance 600, limit 700
        $item = $sale->items()->firstOrFail();
        $other = $this->product(['name' => 'Juice', 'selling_price' => 500]);

        $payload = [
            'return_items' => [['sale_item_id' => $item->id, 'quantity' => 1]],     // 100 credit
            'new_items' => [['product_id' => $other->id, 'quantity' => 1]],         // 500: due 400, all on account
            'payment_method' => 'cash', 'paid' => 0,
        ];

        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", $payload)->assertSessionHasErrors('credit_limit');
        $this->assertSame(0, Exchange::count());
        $this->assertSame(600.0, $this->balance($customer));

        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", $payload + ['override_credit_limit' => 1])->assertRedirect();
        $this->assertSame(1000.0, $this->balance($customer));
    }

    public function test_a_failed_exchange_leaves_the_ledger_untouched(): void
    {
        $user = $this->user(); $product = $this->product(); $customer = $this->customer();
        $sale = $this->creditSale($user, $product, $customer);
        $item = $sale->items()->firstOrFail();
        $empty = $this->product(['name' => 'Gone', 'quantity' => 0]);

        $this->actingAs($user)->post("/sales/{$sale->id}/exchange", [
            'return_items' => [['sale_item_id' => $item->id, 'quantity' => 10]],
            'new_items' => [['product_id' => $empty->id, 'quantity' => 1]],
            'payment_method' => 'cash', 'refund_to' => 'ledger',
        ])->assertSessionHasErrors();

        $this->assertSame(600.0, $this->balance($customer));
        $this->assertSame(0, SaleReturn::count());
        $this->assertSame(1, CustomerLedger::count());                      // only the original sale_credit
    }
}
