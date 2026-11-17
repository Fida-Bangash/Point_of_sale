<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no', 'user_id', 'customer_name', 'subtotal', 'discount', 'total',
    'paid', 'change_amount', 'payment_method', 'status', 'note', 'sold_at',
])]
class Sale extends Model
{
    use Syncable;

    public const PAYMENT_METHODS = [
        'cash'      => 'Cash',
        'card'      => 'Card',
        'easypaisa' => 'Easypaisa',
        'jazzcash'  => 'JazzCash',
        'bank'      => 'Bank Transfer',
    ];

    public const SYNC_COLUMNS = [
        'invoice_no', 'customer_name', 'subtotal', 'discount', 'total', 'paid',
        'change_amount', 'payment_method', 'status', 'note', 'sold_at',
    ];

    public function syncRelations(): array
    {
        return [
            'user_id' => [User::class, 'user_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'subtotal'      => 'decimal:2',
            'discount'      => 'decimal:2',
            'total'         => 'decimal:2',
            'paid'          => 'decimal:2',
            'change_amount' => 'decimal:2',
            'sold_at'       => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }
}