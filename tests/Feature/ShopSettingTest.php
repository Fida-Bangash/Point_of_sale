<?php

namespace Tests\Feature;

use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSettingTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'shop_name' => 'Ali Store', 'phone' => '03001234567', 'city' => 'Karachi',
            'country' => 'Pakistan', 'currency_symbol' => 'Rs', 'currency_code' => 'pkr',
        ], $over);
    }

    public function test_default_row_exists_after_migrate(): void
    {
        $this->assertSame('My Shop', ShopSetting::current()->shop_name);
        $this->assertSame(1, ShopSetting::count());
    }

    public function test_admin_can_save_settings_and_it_is_queued_for_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/company-settings', $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('shop_settings', ['shop_name' => 'Ali Store', 'currency_code' => 'PKR']);
        $this->assertDatabaseHas('sync_queue', ['table_name' => 'shop_settings', 'action' => 'upsert']);
    }

    public function test_shop_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/company-settings', $this->payload(['shop_name' => '']))
            ->assertSessionHasErrors('shop_name');
    }

    public function test_cashier_cannot_open_or_save_settings(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->get('/company-settings')->assertForbidden();
        $this->actingAs($cashier)->put('/company-settings', $this->payload())->assertForbidden();
    }
}