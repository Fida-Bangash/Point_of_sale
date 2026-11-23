<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sale_return_id', 'sale_item_id', 'product_id', 'product_name', 'barcode',
    'quantity', 'unit_price', 'purchase_price', 'refund_amount', 'restocked',
])]
class SaleReturnItem extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'product_name', 'barcode', 'quantity', 'unit_price',
        'purchase_price', 'refund_amount', 'restocked',
    ];

    public function syncRelations(): array
    {
        return [
            'sale_return_id' => [SaleReturn::class, 'sale_return_uuid'],
            'sale_item_id'   => [SaleItem::class, 'sale_item_uuid'],
            'product_id'     => [Product::class, 'product_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:3',
            'unit_price'     => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'refund_amount'  => 'decimal:2',
            'restocked'      => 'boolean',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}