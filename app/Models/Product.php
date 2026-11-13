<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'sku', 'barcode', 'category_id', 'brand_id', 'unit_id',
    'purchase_price', 'selling_price', 'quantity', 'alert_quantity',
    'description', 'image', 'is_active',
])]
class Product extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'name', 'sku', 'barcode', 'purchase_price', 'selling_price',
        'quantity', 'alert_quantity', 'description', 'image', 'is_active',
    ];

    public function syncRelations(): array
    {
        return [
            'category_id' => [Category::class, 'category_uuid'],
            'brand_id'    => [Brand::class, 'brand_uuid'],
            'unit_id'     => [Unit::class, 'unit_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2', 'selling_price' => 'decimal:2',
            'quantity' => 'decimal:3', 'alert_quantity' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }

    public function isLowStock(): bool
    {
        return (float) $this->quantity <= (float) $this->alert_quantity;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? route('media', ['path' => $this->image]) : null;
    }

    public static function trimNumber(float|int|string|null $v): string
    {
        return rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
    }

    public function qty(): string      { return self::trimNumber($this->quantity); }
    public function alertQty(): string { return self::trimNumber($this->alert_quantity); }
}