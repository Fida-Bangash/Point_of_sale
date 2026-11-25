<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'return_no', 'sale_id', 'user_id', 'refund_total', 'ledger_amount', 'exchange_amount', 'refund_method',
    'reason', 'note', 'returned_at',
])]
class SaleReturn extends Model
{
    use Syncable;

    public const SYNC_COLUMNS = [
        'return_no', 'refund_total', 'ledger_amount', 'exchange_amount', 'refund_method', 'reason', 'note', 'returned_at',
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
            'ledger_amount'   => 'decimal:2',
            'exchange_amount' => 'decimal:2',
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

    /** Rows pushed by an older POS carry NULL here, but the local column is NOT NULL. */
    protected function ledgerAmount(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    protected function exchangeAmount(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    /** Money that really left the till for a plain return (not ledger, not exchange credit). */
    public function cashAmount(): float
    {
        return max(0.0, round((float) $this->refund_total - (float) $this->ledger_amount - (float) $this->exchange_amount, 2));
    }

    public function hasLedgerPart(): bool
    {
        return (float) $this->ledger_amount > 0;
    }

    public function refundMethodLabel(): string
    {
        // Returns made as part of an exchange are paid as credit towards the new goods
        if ($this->refund_method === 'exchange') {
            return $this->hasLedgerPart() ? 'Exchange credit + Customer account' : 'Exchange credit';
        }

        // The whole refund went to the customer's account
        if ($this->refund_method === 'ledger') {
            return 'Customer account (Khata)';
        }

        $label = Sale::PAYMENT_METHODS[$this->refund_method] ?? ucfirst((string) $this->refund_method);

        // A cash refund of a credit sale: the unpaid part cannot be paid out, it clears the khata instead
        return $this->hasLedgerPart() ? $label . ' + Customer account' : $label;
    }
}
