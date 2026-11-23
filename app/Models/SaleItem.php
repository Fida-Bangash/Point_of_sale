<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sale_id', 'product_id', 'product_name', 'barcode', 'quantity',
    'unit_price', 'purchase_price', 'line_total',
])]
class SaleItem extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'product_name', 'barcode', 'quantity', 'unit_price', 'purchase_price', 'line_total',
    ];

    public function syncRelations(): array
    {
        return [
            'sale_id'    => [Sale::class, 'sale_uuid'],
            'product_id' => [Product::class, 'product_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:3',
            'unit_price'     => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'line_total'     => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

        public function returnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}