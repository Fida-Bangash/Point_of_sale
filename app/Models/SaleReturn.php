<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'return_no', 'sale_id', 'user_id', 'refund_total', 'refund_method',
    'reason', 'note', 'returned_at',
])]
class SaleReturn extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'return_no', 'refund_total', 'refund_method', 'reason', 'note', 'returned_at',
    ];

    public function syncRelations(): array
    {
        return [
            'sale_id' => [Sale::class, 'sale_uuid'],
            'user_id' => [User::class, 'user_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'refund_total' => 'decimal:2',
            'returned_at'  => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function refundMethodLabel(): string
    {
        // Returns made as part of an exchange are paid as credit towards the new goods
        if ($this->refund_method === 'exchange') {
            return 'Exchange credit';
        }

        return Sale::PAYMENT_METHODS[$this->refund_method] ?? ucfirst((string) $this->refund_method);
    }
}