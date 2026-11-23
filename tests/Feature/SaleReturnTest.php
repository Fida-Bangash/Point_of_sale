<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\SaleService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaleReturnTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $over = []): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Food'],
            ['slug' => 'food', 'is_active' => true]
        );
        $unit = Unit::query()->first()
            ?? Unit::forceCreate(['name' => 'Piece', 'short_name' => 'pc', 'is_active' => true]);

        return Product::create(array_merge([
            'name'           => 'Biscuit',
            'barcode'        => '123456',
            'category_id'    => $category->id,
            'unit_id'        => $unit->id,
            'purchase_price' => 60,
            'selling_price'  => 100,
            'quantity'       => 10,
            'alert_quantity' => 2,
            'is_active'      => true,
        ], $over));
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** Sell $qty of $product through the real SaleService. */
    private function sell(Product $product, float $qty, float $discount = 0, ?User $cashier = null): Sale
    {
        $cashier ??= $this->user('cashier');
        $total = $qty * (float) $product->selling_price - $discount;

        return app(SaleService::class)->create([
            'items'          => [['product_id' => $product->id, 'quantity' => $qty]],
            'discount'       => $discount,
            'paid'           => $total,
            'payment_method' => 'cash',
        ], $cashier->id);
    }

    /** POST a return for one sale line. */
    private function giveBack(User $by, Sale $sale, float $qty, bool $restock = true, array $extra = [])
    {
        return $this->actingAs($by)->post(route('sales.return.store', $sale), array_merge([
            'items' => [[
                'sale_item_id' => $sale->items()->first()->id,
                'quantity'     => $qty,
                'restock'      => $restock ? 1 : 0,
            ]],
        ], $extra));
    }

    public function test_full_return_restocks_refunds_and_marks_the_sale_returned(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $sale    = $this->sell($product, 3);
        $this->assertEquals(7, (float) $product->fresh()->quantity);

        $response = $this->giveBack($manager, $sale, 3, true, ['reason' => 'Wrong item']);

        $return = SaleReturn::firstOrFail();
        $response->assertRedirect(route('sales-returns.receipt', $return));

        $this->assertSame('300.00', $return->refund_total);
        $this->assertSame('cash', $return->refund_method);
        $this->assertSame('Wrong item', $return->reason);
        $this->assertSame($manager->id, $return->user_id);
        $this->assertMatchesRegularExpression('/^RET-\d{8}-0001$/', $return->return_no);

        $line = SaleReturnItem::firstOrFail();
        $this->assertEquals(3, (float) $line->quantity);
        $this->assertSame('300.00', $line->refund_amount);
        $this->assertTrue($line->restocked);

        $this->assertEquals(10, (float) $product->fresh()->quantity);
        $this->assertSame(Sale::STATUS_RETURNED, $sale->fresh()->status);

        foreach (['sale_returns', 'sale_return_items', 'products', 'sales'] as $table) {
            $this->assertDatabaseHas('sync_queue', ['table_name' => $table, 'action' => 'upsert']);
        }
    }

    public function test_partial_returns_are_limited_to_what_is_left(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $sale    = $this->sell($product, 5);               // stock 10 -> 5

        $this->giveBack($manager, $sale, 2);
        $this->assertSame(Sale::STATUS_PARTIALLY_RETURNED, $sale->fresh()->status);
        $this->assertEquals(7, (float) $product->fresh()->quantity);

        // 4 > 3 left: rejected, nothing changes
        $this->giveBack($manager, $sale, 4)->assertSessionHasErrors('items');
        $this->assertSame(1, SaleReturn::count());
        $this->assertEquals(7, (float) $product->fresh()->quantity);

        $this->giveBack($manager, $sale, 3)->assertSessionHasNoErrors();
        $this->assertSame(2, SaleReturn::count());
        $this->assertSame(Sale::STATUS_RETURNED, $sale->fresh()->status);
        $this->assertEquals(10, (float) $product->fresh()->quantity);
        $this->assertSame('500.00', number_format((float) SaleReturn::sum('refund_total'), 2, '.', ''));
    }

    public function test_refund_has_the_bill_discount_taken_off(): void
    {
        $manager = $this->user('manager');
        $sale    = $this->sell($this->makeProduct(), 4, discount: 40);   // 400 - 40 = 360

        $this->giveBack($manager, $sale, 1);
        $this->assertSame('90.00', SaleReturn::first()->refund_total);

        $this->giveBack($manager, $sale, 3);
        $this->assertSame('360.00', number_format((float) SaleReturn::sum('refund_total'), 2, '.', ''));
    }

    public function test_odd_prices_never_refund_more_than_the_customer_paid(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct(['selling_price' => 33.33]);
        $sale    = $this->sell($product, 3, discount: 10);                // 99.99 - 10 = 89.99

        foreach ([1, 1, 1] as $qty) {
            $this->giveBack($manager, $sale, $qty);
        }

        $this->assertSame('89.99', number_format((float) SaleReturn::sum('refund_total'), 2, '.', ''));
        $this->assertSame(Sale::STATUS_RETURNED, $sale->fresh()->status);
    }

    public function test_damaged_goods_are_refunded_but_not_put_back_in_stock(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $sale    = $this->sell($product, 2);                              // stock 8

        $this->giveBack($manager, $sale, 2, restock: false);

        $this->assertEquals(8, (float) $product->fresh()->quantity);
        $this->assertFalse(SaleReturnItem::first()->restocked);
        $this->assertSame('200.00', SaleReturn::first()->refund_total);
    }

    public function test_refund_uses_the_price_paid_not_todays_price(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $sale    = $this->sell($product, 2);

        $product->update(['selling_price' => 500]);                       // price goes up later

        $this->giveBack($manager, $sale, 2);

        $this->assertSame('200.00', SaleReturn::first()->refund_total);
    }

    public function test_refund_method_defaults_to_the_original_payment_method_and_can_be_changed(): void
    {
        $manager = $this->user('manager');
        $sale    = $this->sell($this->makeProduct(), 4);

        $this->giveBack($manager, $sale, 1, true, ['refund_method' => 'easypaisa']);
        $this->giveBack($manager, $sale, 1);

        $this->assertSame(['easypaisa', 'cash'], SaleReturn::orderBy('id')->pluck('refund_method')->all());

        $this->giveBack($manager, $sale, 1, true, ['refund_method' => 'bitcoin'])
            ->assertSessionHasErrors('refund_method');
    }

    public function test_cashier_and_stock_roles_cannot_return(): void
    {
        $sale = $this->sell($this->makeProduct(), 2);

        foreach (['cashier', 'stock'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('sales.return.create', $sale))->assertForbidden();
            $this->giveBack($user, $sale, 1)->assertForbidden();
            $this->actingAs($user)->get(route('sales-returns'))->assertForbidden();
        }

        $this->assertSame(0, SaleReturn::count());

        foreach (['admin', 'manager'] as $role) {
            $this->actingAs($this->user($role))->get(route('sales.return.create', $sale))->assertOk();
        }
    }

    public function test_a_fully_returned_sale_cannot_be_returned_again(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $sale    = $this->sell($product, 1);

        $this->giveBack($manager, $sale, 1)->assertSessionHasNoErrors();

        $this->giveBack($manager, $sale, 1)->assertSessionHasErrors('items');
        $this->actingAs($manager)->get(route('sales.return.create', $sale))
            ->assertRedirect(route('sales-list'));

        $this->assertSame(1, SaleReturn::count());
        $this->assertEquals(10, (float) $product->fresh()->quantity);
    }

    public function test_an_empty_return_or_a_line_from_another_sale_is_rejected(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct();
        $saleA   = $this->sell($product, 2);
        $saleB   = $this->sell($product, 2);

        $this->giveBack($manager, $saleA, 0)->assertSessionHasErrors('items');

        // line of sale B posted against sale A
        $this->actingAs($manager)->post(route('sales.return.store', $saleA), [
            'items' => [['sale_item_id' => $saleB->items()->first()->id, 'quantity' => 1, 'restock' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, SaleReturn::count());
    }

    public function test_return_window_can_be_enforced(): void
    {
        config(['pos.returns.window_days' => 7]);

        $manager = $this->user('manager');
        $sale    = $this->sell($this->makeProduct(), 2);
        $sale->update(['sold_at' => now()->subDays(10)]);

        $this->giveBack($manager, $sale, 1)->assertSessionHasErrors('items');
        $this->assertSame(0, SaleReturn::count());

        $sale->update(['sold_at' => now()->subDays(2)]);
        $this->giveBack($manager, $sale, 1)->assertSessionHasNoErrors();
        $this->assertSame(1, SaleReturn::count());
    }

    public function test_list_and_receipt_pages_show_the_return(): void
    {
        $manager = $this->user('manager');
        $sale    = $this->sell($this->makeProduct(), 2);

        $this->giveBack($manager, $sale, 1);
        $return = SaleReturn::first();

        $this->actingAs($manager)->get(route('sales-returns'))
            ->assertOk()->assertSee($return->return_no)->assertSee($sale->invoice_no);

        $this->actingAs($manager)->get(route('sales-returns.receipt', $return))
            ->assertOk()->assertSee($return->return_no)->assertSee('RETURN / REFUND')->assertSee('My Shop');

        $this->actingAs($manager)->get(route('sales-list'))
            ->assertOk()->assertSee('Partly returned');
    }

    public function test_returns_are_registered_for_sync_after_the_things_they_point_to(): void
    {
        $tables = array_keys(SyncService::MODELS);
        $pos    = fn (string $t) => array_search($t, $tables, true);

        $this->assertNotFalse($pos('sale_returns'));
        $this->assertNotFalse($pos('sale_return_items'));
        $this->assertLessThan($pos('sale_returns'), $pos('sales'));
        $this->assertLessThan($pos('sale_returns'), $pos('users'));
        $this->assertLessThan($pos('sale_return_items'), $pos('sale_returns'));
        $this->assertLessThan($pos('sale_return_items'), $pos('sale_items'));
        $this->assertLessThan($pos('sale_return_items'), $pos('products'));
    }

    public function test_a_return_can_be_restored_from_server_rows_without_touching_stock(): void
    {
        $manager = $this->user('manager');
        $product = $this->makeProduct(['quantity' => 10]);
        $sale    = $this->sell($product, 2);                              // stock 8
        $item    = $sale->items()->first();

        $returnUuid = (string) Str::uuid();
        $stamp      = ['created_at' => '2026-11-21 10:00:00', 'updated_at' => '2026-11-21 10:00:00'];

        SaleReturn::applyFromServer($stamp + [
            'uuid' => $returnUuid, 'return_no' => 'RET-20261121-0009', 'sale_uuid' => $sale->uuid,
            'user_uuid' => $manager->uuid, 'refund_total' => '100.00', 'refund_method' => 'cash',
            'reason' => 'Damaged', 'note' => null, 'returned_at' => '2026-11-21 10:00:00',
        ]);

        SaleReturnItem::applyFromServer($stamp + [
            'uuid' => (string) Str::uuid(), 'sale_return_uuid' => $returnUuid, 'sale_item_uuid' => $item->uuid,
            'product_uuid' => $product->uuid, 'product_name' => 'Biscuit', 'barcode' => '123456',
            'quantity' => '1.000', 'unit_price' => '100.00', 'purchase_price' => '60.00',
            'refund_amount' => '100.00', 'restocked' => 1,
        ]);

        $return = SaleReturn::where('uuid', $returnUuid)->firstOrFail();
        $line   = SaleReturnItem::firstOrFail();

        $this->assertSame($sale->id, $return->sale_id);
        $this->assertSame($manager->id, $return->user_id);
        $this->assertSame($return->id, $line->sale_return_id);
        $this->assertSame($item->id, $line->sale_item_id);
        $this->assertSame($product->id, $line->product_id);
        $this->assertTrue($line->restocked);

        // Restoring history must never move stock: quantity comes from the products table itself
        $this->assertEquals(8, (float) $product->fresh()->quantity);
    }
}