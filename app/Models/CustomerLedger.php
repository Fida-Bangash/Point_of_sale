<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

#[Fillable([
    'customer_id', 'type', 'amount', 'reversal_of_id', 'ref_type', 'ref_uuid',
    'note', 'user_id', 'entry_date', 'payment_method', 'receipt_no', 'previous_balance',
])]
class CustomerLedger extends Model
{
    use Syncable;

    protected $table = 'customer_ledger';

    public const TYPE_OPENING     = 'opening';
    public const TYPE_SALE_CREDIT = 'sale_credit';
    public const TYPE_PAYMENT     = 'payment';
    public const TYPE_RETURN      = 'return_credit';
    public const TYPE_ADJUSTMENT  = 'adjustment';
    public const TYPE_REVERSAL    = 'reversal';

    public const TYPES = [
        self::TYPE_OPENING     => 'Opening balance',
        self::TYPE_SALE_CREDIT => 'Sale on credit',
        self::TYPE_PAYMENT     => 'Payment received',
        self::TYPE_RETURN      => 'Return credit',
        self::TYPE_ADJUSTMENT  => 'Adjustment',
        self::TYPE_REVERSAL    => 'Reversal',
    ];

    /** Ways a customer can pay off their balance. */
    public const PAYMENT_METHODS = [
        'cash'      => 'Cash',
        'bank'      => 'Bank Transfer',
        'easypaisa' => 'Easypaisa',
        'jazzcash'  => 'JazzCash',
    ];

    public const SYNC_COLUMNS = [
        'type', 'amount', 'ref_type', 'ref_uuid', 'note', 'entry_date',
        'payment_method', 'receipt_no', 'previous_balance',
    ];


    public const APPEND_ONLY = true;

    /**
     * Ledger entries are append-only. A mistake is fixed with a reversal entry,
     * never by editing or deleting history.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Ledger entries cannot be edited. Create a reversal instead.');
        });

        static::deleting(function () {
            throw new LogicException('Ledger entries cannot be deleted. Create a reversal instead.');
        });
    }

    public function syncRelations(): array
    {
        return [
            'customer_id'    => [Customer::class, 'customer_uuid'],
            'user_id'        => [User::class, 'user_uuid'],
            'reversal_of_id' => [self::class, 'reversal_of_uuid'],
        ];
    }

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'entry_date'       => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The entry that cancelled this one, if any. */
    public function reversedBy(): HasOne
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }

    /** The entry this one cancels (only set on `reversal` entries). */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function isPayment(): bool
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    public function methodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ($this->payment_method ? ucfirst($this->payment_method) : '-');
    }

    /** The balance right after this entry was written (only known for entries that stored previous_balance). */
    public function balanceAfter(): ?float
    {
        return $this->previous_balance === null
            ? null
            : round((float) $this->previous_balance + (float) $this->amount, 2);
    }
}
