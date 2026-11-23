<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaleTest extends TestCase
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

    private function checkout(User $user, array $payload)
    {
        return $this->actingAs($user)->postJson('/pos/checkout', array_merge([
            'payment_method' => 'cash',
        ], $payload));
    }

    public function test_cashier_can_open_the_pos_screen_but_stock_role_cannot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']))->get('/pos')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'stock']))->get('/pos')->assertForbidden();
    }

    public function test_sale_is_saved_stock_is_reduced_and_everything_is_queued_for_sync(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = $this->makeProduct();

        $this->checkout($cashier, [
            'items'    => [['product_id' => $product->id, 'quantity' => 3]],
            'discount' => 50,
            'paid'     => 300,
        ])->assertOk()->assertJsonStructure(['sale_id', 'invoice_no', 'receipt_url']);

        $sale = Sale::first();
        $this->assertSame('300.00', $sale->subtotal);
        $this->assertSame('250.00', $sale->total);
        $this->assertSame('50.00', $sale->change_amount);
        $this->assertSame($cashier->id, $sale->user_id);
        $this->assertSame(1, SaleItem::count());
        $this->assertEquals(7, (float) $product->fresh()->quantity);

        foreach (['sales', 'sale_items', 'products'] as $table) {
            $this->assertDatabaseHas('sync_queue', ['table_name' => $table, 'action' => 'upsert']);
        }
    }

    public function test_sale_is_rejected_when_stock_is_not_enough(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = $this->makeProduct(['quantity' => 2]);

        $this->checkout($cashier, [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
            'paid'  => 500,
        ])->assertStatus(422);

        $this->assertSame(0, Sale::count());
        $this->assertEquals(2, (float) $product->fresh()->quantity);
    }

    public function test_sale_is_rejected_when_paid_is_less_than_total(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = $this->makeProduct();

        $this->checkout($cashier, [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'paid'  => 100,
        ])->assertStatus(422);

        $this->assertSame(0, Sale::count());
        $this->assertEquals(10, (float) $product->fresh()->quantity);
    }

    public function test_receipt_shows_invoice_and_shop_name(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = $this->makeProduct();

        $this->checkout($cashier, [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'paid'  => 100,
        ])->assertOk();

        $sale = Sale::first();

        $this->actingAs($cashier)->get(route('sales.receipt', $sale))
            ->assertOk()
            ->assertSee($sale->invoice_no)
            ->assertSee('My Shop');

        $this->actingAs($cashier)->get('/sales-list')->assertOk()->assertSee($sale->invoice_no);
    }

    public function test_sales_are_registered_for_sync_in_the_right_order(): void
    {
        $tables = array_keys(SyncService::MODELS);

        $this->assertContains('sales', $tables);
        $this->assertContains('sale_items', $tables);
        $this->assertLessThan(array_search('sales', $tables), array_search('users', $tables));
        $this->assertLessThan(array_search('sale_items', $tables), array_search('sales', $tables));
        $this->assertLessThan(array_search('sale_items', $tables), array_search('products', $tables));
    }

    public function test_sale_and_items_can_be_restored_from_server_rows(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = $this->makeProduct();
        $saleUuid = (string) Str::uuid();
        $stamp = ['created_at' => '2026-11-16 10:00:00', 'updated_at' => '2026-11-16 10:00:00'];

        Sale::applyFromServer($stamp + [
            'uuid' => $saleUuid, 'invoice_no' => 'INV-20261116-0001', 'user_uuid' => $cashier->uuid,
            'customer_name' => null, 'subtotal' => '100.00', 'discount' => '0.00', 'total' => '100.00',
            'paid' => '100.00', 'change_amount' => '0.00', 'payment_method' => 'cash',
            'status' => 'completed', 'note' => null, 'sold_at' => '2026-11-16 10:00:00',
        ]);

        SaleItem::applyFromServer($stamp + [
            'uuid' => (string) Str::uuid(), 'sale_uuid' => $saleUuid, 'product_uuid' => $product->uuid,
            'product_name' => 'Biscuit', 'barcode' => '123456', 'quantity' => '1.000',
            'unit_price' => '100.00', 'purchase_price' => '60.00', 'line_total' => '100.00',
        ]);

        $sale = Sale::first();
        $item = SaleItem::first();

        $this->assertSame($cashier->id, $sale->user_id);
        $this->assertSame($sale->id, $item->sale_id);
        $this->assertSame($product->id, $item->product_id);
    }

        public function test_pos_products_hide_cost_and_margin_from_cashier_but_not_from_admin(): void
    {
        $this->makeProduct();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $admin   = User::factory()->create(['role' => 'admin']);

        $cashierRows = $this->actingAs($cashier)->getJson('/pos/products')->assertOk()->json();
        $this->assertSame('Biscuit', $cashierRows[0]['name']);
        $this->assertArrayNotHasKey('profit', $cashierRows[0]);
        $this->assertArrayNotHasKey('margin_pct', $cashierRows[0]);

        $adminRows = $this->actingAs($admin)->getJson('/pos/products')->assertOk()->json();
        $this->assertEquals(40, $adminRows[0]['profit']);
        $this->assertEquals(40, $adminRows[0]['margin_pct']);
    }

    public function test_pos_products_put_sold_out_last_and_can_filter_by_category(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->makeProduct(['name' => 'Aaa Sold Out', 'barcode' => '1', 'quantity' => 0]);
        $this->makeProduct(['name' => 'Zzz In Stock', 'barcode' => '2', 'quantity' => 5]);

        $rows = $this->actingAs($cashier)->getJson('/pos/products')->assertOk()->json();
        $this->assertSame(['Zzz In Stock', 'Aaa Sold Out'], array_column($rows, 'name'));

        $other = Category::create(['name' => 'Drinks', 'slug' => 'drinks', 'is_active' => true]);
        $this->actingAs($cashier)->getJson('/pos/products?category=' . $other->id)
            ->assertOk()->assertExactJson([]);
    }
}