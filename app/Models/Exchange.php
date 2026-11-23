<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exchange_no', 'original_sale_id', 'sale_return_id', 'new_sale_id', 'user_id',
    'returned_value', 'new_value', 'credit_applied', 'cash_in', 'cash_out',
    'payment_method', 'note', 'exchanged_at',
])]
class Exchange extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'exchange_no', 'returned_value', 'new_value', 'credit_applied',
        'cash_in', 'cash_out', 'payment_method', 'note', 'exchanged_at',
    ];

    public function syncRelations(): array
    {
        return [
            'original_sale_id' => [Sale::class, 'original_sale_uuid'],
            'sale_return_id'   => [SaleReturn::class, 'sale_return_uuid'],
            'new_sale_id'      => [Sale::class, 'new_sale_uuid'],
            'user_id'          => [User::class, 'user_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'returned_value' => 'decimal:2',
            'new_value'      => 'decimal:2',
            'credit_applied' => 'decimal:2',
            'cash_in'        => 'decimal:2',
            'cash_out'       => 'decimal:2',
            'exchanged_at'   => 'datetime',
        ];
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function newSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'new_sale_id');
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Positive: customer paid more. Negative: shop paid back. Zero: even. */
    public function difference(): float
    {
        return round((float) $this->new_value - (float) $this->returned_value, 2);
    }

    public function paymentLabel(): string
    {
        return Sale::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }
}