<?php

namespace Tests\Feature;

use App\Models\ShopSetting;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopSettingSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_settings_is_registered_for_sync_with_logo_as_image(): void
    {
        $this->assertArrayHasKey('shop_settings', SyncService::MODELS);
        $this->assertSame('logo', SyncService::IMAGE_COLUMNS['shop_settings']);
    }

    public function test_to_sync_contains_code_and_shop_fields(): void
    {
        $shop = ShopSetting::current();
        $shop->update(['shop_name' => 'Ali Store']);

        $row = $shop->fresh()->toSync();

        $this->assertSame('main', $row['code']);
        $this->assertSame('Ali Store', $row['shop_name']);
        $this->assertSame($shop->uuid, $row['uuid']);
    }

    public function test_restore_reuses_default_row_by_code(): void
    {
        $uuid = (string) Str::uuid();

        ShopSetting::applyFromServer([
            'uuid' => $uuid, 'code' => 'main', 'shop_name' => 'Ali Store', 'phone' => null,
            'email' => null, 'address' => 'Shop 5', 'city' => 'Karachi', 'country' => 'Pakistan',
            'tax_number' => null, 'logo' => null, 'currency_symbol' => 'Rs',
            'currency_code' => 'PKR', 'receipt_footer' => null,
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);

        $this->assertSame(1, ShopSetting::count());
        $this->assertSame($uuid, ShopSetting::first()->uuid);
        $this->assertSame('Ali Store', ShopSetting::first()->shop_name);
    }
}