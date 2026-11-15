<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'shop_name', 'phone', 'email', 'address', 'city', 'country', 'tax_number',
    'logo', 'currency_symbol', 'currency_code', 'receipt_footer',
])]
class ShopSetting extends Model
{
    use Syncable;


    public const SYNC_COLUMNS = [
        'code', 'shop_name', 'phone', 'email', 'address', 'city', 'country',
        'tax_number', 'logo', 'currency_symbol', 'currency_code', 'receipt_footer',
    ];

    public const NATURAL_KEY = ['code'];

    public const DEFAULTS = [
        'shop_name'       => 'My Shop',
        'country'         => 'Pakistan',
        'currency_symbol' => 'Rs',
        'currency_code'   => 'PKR',
        'receipt_footer'  => 'Thank you for shopping with us!',
    ];

   
    public static function current(): self
    {
        return static::query()->first() ?? static::create(self::DEFAULTS);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? route('media', ['path' => $this->logo]) : null;
    }


    public function money(float|int|string|null $amount): string
    {
        return $this->currency_symbol . ' ' . number_format((float) $amount, 2);
    }

  
    public function fullAddress(): string
    {
        return collect([$this->address, $this->city, $this->country])
            ->filter()
            ->implode(', ');
    }
}
