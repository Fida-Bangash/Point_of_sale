<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A paused cart. It lives only in this PC's local database and is
 * deliberately NOT cloud-synced (it is temporary data).
 *
 * `items` is a snapshot, so a bill can still be matched to products by
 * uuid / barcode / sku if the catalog ever moves to a shared server.
 */
class HeldBill extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'customer_name', 'discount', 'total', 'items', 'held_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'items'      => 'array',
            'discount'   => 'decimal:2',
            'total'      => 'decimal:2',
            'held_at'    => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Admin and manager see every held bill; everyone else sees only their own. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->hasRole('admin', 'manager') ? $query : $query->where('user_id', $user->id);
    }

    public static function purgeExpired(): void
    {
        static::where('expires_at', '<', now())->delete();
    }
}