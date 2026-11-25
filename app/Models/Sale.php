<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no', 'user_id', 'customer_name', 'subtotal', 'discount', 'total',
    'paid', 'change_amount', 'credit_amount', 'previous_balance', 'exchange_credit', 'customer_id',
    'payment_method', 'status', 'note', 'sold_at',
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

    /**
     * Checkout-only method: "the rest goes on the customer's account". It is deliberately not in
     * PAYMENT_METHODS, because that list is also used for refunds and exchanges, where it makes no sense.
     */
    public const CREDIT_METHOD = 'credit';

    public const STATUS_COMPLETED          = 'completed';
    public const STATUS_PARTIALLY_RETURNED = 'partially_returned';
    public const STATUS_RETURNED           = 'returned';

    public const SYNC_COLUMNS = [
        'invoice_no', 'customer_name', 'subtotal', 'discount', 'total', 'paid',
        'change_amount', 'credit_amount', 'previous_balance', 'exchange_credit',
        'payment_method', 'status', 'note', 'sold_at',
    ];

    public function syncRelations(): array
    {
        return [
            'user_id' => [User::class, 'user_uuid'],
            'customer_id' => [Customer::class, 'customer_uuid'],
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
            'credit_amount'    => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'exchange_credit' => 'decimal:2',
            'sold_at'       => 'datetime',
        ];
    }

      /**
     * A server row pushed by an older POS (before exchanges existed) can carry NULL here.
     * The local column is NOT NULL, so restoring such a row must store 0 instead.
     */
    protected function exchangeCredit(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    /** Same reason as exchangeCredit(): rows pushed by an older POS carry NULL, the local column is NOT NULL. */
    protected function creditAmount(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    protected function previousBalance(): Attribute
    {
        return Attribute::make(set: fn ($value) => $value ?? 0);
    }

    /** Every method that can be chosen at the checkout (the normal ones plus "credit"). */
    public static function checkoutMethods(): array
    {
        return self::PAYMENT_METHODS + [self::CREDIT_METHOD => 'Credit (Udhaar)'];
    }

    public function customer(): BelongsTo
    {
    return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /** Exchanges in which goods of this sale were handed back. */
    public function exchanges(): HasMany
    {
        return $this->hasMany(Exchange::class, 'original_sale_id');
    }

    /** True when part of this sale was paid with goods handed back (it is the "new goods" side of an exchange). */
    public function isExchangeSale(): bool
    {
        return (float) $this->exchange_credit > 0;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_RETURNED           => 'Returned',
            self::STATUS_PARTIALLY_RETURNED => 'Partly returned',
            default                         => 'Completed',
        };
    }

    /** Bootstrap badge colour for the status */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_RETURNED           => 'bg-danger',
            self::STATUS_PARTIALLY_RETURNED => 'bg-warning',
            default                         => 'bg-success',
        };
    }

    /** True when part (or all) of this sale was put on the customer's account. */
    public function hasCredit(): bool
    {
        return (float) $this->credit_amount > 0;
    }

    /** The customer's balance right after this sale. */
    public function balanceAfter(): float
    {
        return round((float) $this->previous_balance + (float) $this->credit_amount, 2);
    }

    /** The receipt prints the account block for a registered customer who has a balance or took credit. */
    public function showsAccountSummary(): bool
    {
        return $this->customer_id !== null
            && ($this->hasCredit() || abs((float) $this->previous_balance) > 0.0001);
    }

    /** Name of the method used for the money that was actually received ("Cash", "Easypaisa", ...). */
    public function methodLabel(): string
    {
        if ($this->payment_method === self::CREDIT_METHOD) {
            return 'Credit';
        }

        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    /** Label for lists: a part-paid sale reads "Cash + Credit". */
    public function paymentLabel(): string
    {
        if ($this->hasCredit() && $this->payment_method !== self::CREDIT_METHOD) {
            return $this->methodLabel() . ' + Credit';
        }

        return $this->methodLabel();
    }
}