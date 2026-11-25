<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exchange_no', 'original_sale_id', 'sale_return_id', 'new_sale_id', 'user_id',
    'returned_value', 'new_value', 'credit_applied', 'cash_in', 'cash_out', 'ledger_in', 'ledger_out',
    'payment_method', 'note', 'exchanged_at',
])]
class Exchange extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'exchange_no', 'returned_value', 'new_value', 'credit_applied',
        'cash_in', 'cash_out', 'ledger_in', 'ledger_out', 'payment_method', 'note', 'exchanged_at',
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
            'ledger_in'      => 'decimal:2',
            'ledger_out'     => 'decimal:2',
            'exchanged_at'   => 'datetime',
        ];
    }

    /** Rows pushed by an older POS carry NULL here, but the local column is NOT NULL. */
    protected function ledgerIn(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    protected function ledgerOut(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    /** What the customer had to pay in total: cash handed over plus what went on the account. */
    public function totalDue(): float
    {
        return round((float) $this->cash_in + (float) $this->ledger_in, 2);
    }

    /** What the shop owed the customer in total: cash paid out plus what was credited to the account. */
    public function totalRefund(): float
    {
        return round((float) $this->cash_out + (float) $this->ledger_out, 2);
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