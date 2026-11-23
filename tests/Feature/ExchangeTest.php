<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Exchange;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Unit;
use App\Models\User;
use App\Services\SaleService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExchangeTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $name, string $barcode, float $price, float $stock = 10): Product
    {
        $category = Category::firstOrCreate(['name' => 'Drinks'], ['slug' => 'drinks', 'is_active' => true]);
        $unit     = Unit::query()->first()
            ?? Unit::forceCreate(['name' => 'Piece', 'short_name' => 'pc', 'is_active' => true]);

        return Product::create([
            'name'           => $name,
            'barcode'        => $barcode,
            'category_id'    => $category->id,
            'unit_id'        => $unit->id,
            'purchase_price' => $price * 0.6,
            'selling_price'  => $price,
            'quantity'       => $stock,
            'alert_quantity' => 1,
            'is_active'      => true,
        ]);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function sell(Product $product, float $qty, float $discount = 0): Sale
    {
        $total = $qty * (float) $product->selling_price - $discount;

        return app(SaleService::class)->create([
            'items'          => [['product_id' => $product->id, 'quantity' => $qty]],
            'discount'       => $discount,
            'paid'           => $total,
            'payment_method' => 'cash',
        ], $this->user('cashier')->id);
    }

    /** POST an exchange: give back $returnQty of the sale's line, take $newQty of $newProduct. */
    private function exchange(User $by, Sale $sale, float $returnQty, Product $newProduct, float $newQty = 1, array $extra = [])
    {
        return $this->actingAs($by)->post(route('sales.exchange.store', $sale), array_merge([
            'return_items'   => [[
                'sale_item_id' => $sale->items()->first()->id,
                'quantity'     => $returnQty,
                'restock'      => 1,
            ]],
            'new_items'      => [['product_id' => $newProduct->id, 'quantity' => $newQty]],
            'payment_method' => 'cash',
        ], $extra));
    }

    public function test_customer_pays_the_difference_when_the_new_goods_cost_more(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('7Up', 'A1', 100);
        $b       = $this->makeProduct('Pepsi Large', 'B1', 250);
        $sale    = $this->sell($a, 2);                                   // A stock 8

        $response = $this->exchange($manager, $sale, 1, $b, 1, ['paid' => 200]);

        $exchange = Exchange::firstOrFail();
        $response->assertRedirect(route('exchanges.receipt', $exchange));

        $this->assertSame('100.00', $exchange->returned_value);
        $this->assertSame('250.00', $exchange->new_value);
        $this->assertSame('100.00', $exchange->credit_applied);
        $this->assertSame('150.00', $exchange->cash_in);
        $this->assertSame('0.00', $exchange->cash_out);
        $this->assertSame(150.0, $exchange->difference());
        $this->assertMatchesRegularExpression('/^EXC-\d{8}-0001$/', $exchange->exchange_no);

        $newSale = $exchange->newSale;
        $this->assertSame('250.00', $newSale->total);
        $this->assertSame('100.00', $newSale->exchange_credit);
        $this->assertSame('200.00', $newSale->paid);
        $this->assertSame('50.00', $newSale->change_amount);          // 200 received - 150 due
        $this->assertTrue($newSale->isExchangeSale());

        $return = $exchange->saleReturn;
        $this->assertSame('exchange', $return->refund_method);
        $this->assertSame('100.00', $return->refund_total);

        $this->assertEquals(9, (float) $a->fresh()->quantity);          // 1 came back
        $this->assertEquals(9, (float) $b->fresh()->quantity);          // 1 went out
        $this->assertSame($sale->id, $exchange->original_sale_id);
        $this->assertSame(Sale::STATUS_PARTIALLY_RETURNED, $sale->fresh()->status);

        foreach (['exchanges', 'sale_returns', 'sales', 'products'] as $table) {
            $this->assertDatabaseHas('sync_queue', ['table_name' => $table, 'action' => 'upsert']);
        }
    }

    public function test_shop_pays_back_the_difference_when_the_new_goods_cost_less(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Big Bottle', 'A1', 100);
        $b       = $this->makeProduct('Small Bottle', 'B1', 60);
        $sale    = $this->sell($a, 2);

        $this->exchange($manager, $sale, 1, $b)->assertSessionHasNoErrors();

        $exchange = Exchange::firstOrFail();
        $this->assertSame('100.00', $exchange->returned_value);
        $this->assertSame('60.00', $exchange->new_value);
        $this->assertSame('60.00', $exchange->credit_applied);
        $this->assertSame('0.00', $exchange->cash_in);
        $this->assertSame('40.00', $exchange->cash_out);
        $this->assertSame(-40.0, $exchange->difference());

        // The new goods are fully paid by credit, so no cash had to be received
        $this->assertSame('60.00', $exchange->newSale->exchange_credit);
        $this->assertSame('0.00', $exchange->newSale->paid);
    }

    public function test_an_even_exchange_moves_no_money(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Fanta', 'B1', 100);
        $sale    = $this->sell($a, 1);

        $this->exchange($manager, $sale, 1, $b)->assertSessionHasNoErrors();

        $exchange = Exchange::firstOrFail();
        $this->assertSame('0.00', $exchange->cash_in);
        $this->assertSame('0.00', $exchange->cash_out);
        $this->assertSame(0.0, $exchange->difference());
        $this->assertSame(Sale::STATUS_RETURNED, $sale->fresh()->status);
    }

    public function test_the_bill_discount_of_the_original_sale_is_respected(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Juice', 'B1', 90);
        $sale    = $this->sell($a, 4, discount: 40);                    // customer paid 360, so 90 each

        $this->exchange($manager, $sale, 1, $b)->assertSessionHasNoErrors();

        $exchange = Exchange::firstOrFail();
        $this->assertSame('90.00', $exchange->returned_value);          // not 100
        $this->assertSame('0.00', $exchange->cash_in);
        $this->assertSame('0.00', $exchange->cash_out);
    }

    public function test_discount_on_the_new_goods_is_applied(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Juice', 'B1', 300);
        $sale    = $this->sell($a, 1);

        $this->exchange($manager, $sale, 1, $b, 1, ['discount' => 50, 'paid' => 150])->assertSessionHasNoErrors();

        $exchange = Exchange::firstOrFail();
        $this->assertSame('250.00', $exchange->new_value);              // 300 - 50
        $this->assertSame('150.00', $exchange->cash_in);                // 250 - 100 credit
    }

    public function test_nothing_is_saved_when_the_new_goods_are_out_of_stock(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Rare Drink', 'B1', 100, stock: 1);
        $sale    = $this->sell($a, 2);                                   // A stock 8

        $this->exchange($manager, $sale, 1, $b, 5)->assertSessionHasErrors('items');

        $this->assertSame(0, Exchange::count());
        $this->assertSame(0, SaleReturn::count());
        $this->assertSame(1, Sale::count());
        $this->assertEquals(8, (float) $a->fresh()->quantity);          // the return was rolled back
        $this->assertEquals(1, (float) $b->fresh()->quantity);
        $this->assertSame(Sale::STATUS_COMPLETED, $sale->fresh()->status);
    }

    public function test_goods_handed_back_are_counted_before_the_stock_of_the_new_goods_is_checked(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Shirt M', 'A1', 100, stock: 1);
        $sale    = $this->sell($a, 1);                                   // A stock 0

        // Swap the shirt for the same product (e.g. another size of the same item)
        $this->exchange($manager, $sale, 1, $a)->assertSessionHasNoErrors();

        $this->assertSame(1, Exchange::count());
        $this->assertEquals(0, (float) $a->fresh()->quantity);
    }

    public function test_the_customer_must_pay_the_difference(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Juice', 'B1', 250);
        $sale    = $this->sell($a, 2);

        $this->exchange($manager, $sale, 1, $b, 1, ['paid' => 100])->assertSessionHasErrors('paid');

        $this->assertSame(0, Exchange::count());
        $this->assertSame(0, SaleReturn::count());
        $this->assertEquals(8, (float) $a->fresh()->quantity);
    }

    public function test_an_exchange_needs_goods_coming_back_and_new_goods(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Juice', 'B1', 100);
        $sale    = $this->sell($a, 2);

        $this->exchange($manager, $sale, 0, $b)->assertSessionHasErrors('items');

        $this->actingAs($manager)->post(route('sales.exchange.store', $sale), [
            'return_items'   => [['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1, 'restock' => 1]],
            'new_items'      => [],
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('new_items');

        $this->assertSame(0, Exchange::count());
    }

    public function test_only_admin_and_manager_can_exchange(): void
    {
        $a    = $this->makeProduct('Cola', 'A1', 100);
        $b    = $this->makeProduct('Juice', 'B1', 100);
        $sale = $this->sell($a, 2);

        foreach (['cashier', 'stock'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('sales.exchange.create', $sale))->assertForbidden();
            $this->exchange($user, $sale, 1, $b)->assertForbidden();
            $this->actingAs($user)->get(route('exchanges'))->assertForbidden();
        }

        $this->assertSame(0, Exchange::count());

        foreach (['admin', 'manager'] as $role) {
            $this->actingAs($this->user($role))->get(route('sales.exchange.create', $sale))->assertOk();
        }
    }

    public function test_the_pos_checkout_cannot_be_used_to_fake_an_exchange_credit(): void
    {
        $cashier = $this->user('cashier');
        $a       = $this->makeProduct('Cola', 'A1', 100);

        $this->actingAs($cashier)->postJson(route('pos.checkout'), [
            'items'           => [['product_id' => $a->id, 'quantity' => 1]],
            'paid'            => 0,
            'payment_method'  => 'cash',
            'exchange_credit' => 100,
        ])->assertStatus(422)->assertJsonValidationErrors('paid');

        $this->assertSame(0, Sale::count());
    }

    public function test_list_receipt_and_sales_list_pages_show_the_exchange(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('7Up', 'A1', 100);
        $b       = $this->makeProduct('Pepsi Large', 'B1', 250);
        $sale    = $this->sell($a, 2);

        $this->exchange($manager, $sale, 1, $b, 1, ['paid' => 150]);
        $exchange = Exchange::firstOrFail();

        $this->actingAs($manager)->get(route('exchanges'))
            ->assertOk()->assertSee($exchange->exchange_no);

        $this->actingAs($manager)->get(route('exchanges.receipt', $exchange))
            ->assertOk()->assertSee($exchange->exchange_no)->assertSee('GOODS RETURNED')
            ->assertSee('NEW GOODS')->assertSee('CUSTOMER PAYS')->assertSee('Pepsi Large');

        $this->actingAs($manager)->get(route('sales-list'))
            ->assertOk()->assertSee('Exchange');
    }

    public function test_exchanges_are_registered_for_sync_after_the_things_they_point_to(): void
    {
        $tables = array_keys(SyncService::MODELS);
        $pos    = fn (string $t) => array_search($t, $tables, true);

        $this->assertNotFalse($pos('exchanges'));
        $this->assertLessThan($pos('exchanges'), $pos('sales'));
        $this->assertLessThan($pos('exchanges'), $pos('sale_returns'));
        $this->assertLessThan($pos('exchanges'), $pos('users'));
        $this->assertContains('exchange_credit', Sale::SYNC_COLUMNS);
    }

    public function test_an_exchange_can_be_restored_from_server_rows(): void
    {
        $manager = $this->user('manager');
        $a       = $this->makeProduct('Cola', 'A1', 100);
        $b       = $this->makeProduct('Juice', 'B1', 100);
        $sale    = $this->sell($a, 2);

        $this->exchange($manager, $sale, 1, $b);
        $source = Exchange::firstOrFail();

        // Rebuild the same exchange from "server" rows after its local copy is gone
        $uuid = (string) Str::uuid();
        $row  = [
            'uuid' => $uuid, 'exchange_no' => 'EXC-20261122-0099',
            'original_sale_uuid' => $sale->uuid, 'sale_return_uuid' => $source->saleReturn->uuid,
            'new_sale_uuid' => $source->newSale->uuid, 'user_uuid' => $manager->uuid,
            'returned_value' => '100.00', 'new_value' => '100.00', 'credit_applied' => '100.00',
            'cash_in' => '0.00', 'cash_out' => '0.00', 'payment_method' => 'cash', 'note' => null,
            'exchanged_at' => '2026-11-22 10:00:00',
            'created_at' => '2026-11-22 10:00:00', 'updated_at' => '2026-11-22 10:00:00',
        ];
        Exchange::applyFromServer($row);

        $restored = Exchange::where('uuid', $uuid)->firstOrFail();
        $this->assertSame($sale->id, $restored->original_sale_id);
        $this->assertSame($source->sale_return_id, $restored->sale_return_id);
        $this->assertSame($source->new_sale_id, $restored->new_sale_id);
        $this->assertSame($manager->id, $restored->user_id);

        // Restoring history never moves stock
        $this->assertEquals(10 - 2 + 1, (float) $a->fresh()->quantity);
        $this->assertEquals(9, (float) $b->fresh()->quantity);
    }
}