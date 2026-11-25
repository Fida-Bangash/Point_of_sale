<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'address', 'city', 'credit_limit', 'is_active', 'notes'])]
class Customer extends Model
{
    use Syncable;

    /** Pakistani mobile number, 11 digits, after normalizePhone(): 03001234567 */
    public const PHONE_REGEX = '/^03\d{9}$/';

    public const SYNC_COLUMNS = ['name', 'phone', 'address', 'city', 'credit_limit', 'is_active', 'notes'];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active'    => 'boolean',
        ];
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /** "0300-123 4567" becomes "03001234567". */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s\-]/', '', trim($phone));
    }

    /** Current balance: positive = the customer owes the shop. Always computed from the ledger. */
    public function currentBalance(): float
    {
        return round((float) $this->ledger()->sum('amount'), 2);
    }

    /**
     * Compact shape used by the POS screen.
     *
     * @return array{id:int, name:string, phone:string, balance:float, credit_limit:?float, is_active:bool}
     */
    public function toPosArray(): array
    {
        $balance = array_key_exists('balance', $this->attributes)
            ? round((float) $this->attributes['balance'], 2)
            : $this->currentBalance();

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'balance'      => $balance,
            'credit_limit' => $this->credit_limit === null ? null : (float) $this->credit_limit,
            'is_active'    => (bool) $this->is_active,
        ];
    }

    /**
     * Convert Pakistani mobile number into WhatsApp format.
     * Example: 03001234567 -> 923001234567
     */
    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->phone);

        return preg_match('/^03\d{9}$/', $digits)
            ? '92' . substr($digits, 1)
            : null;
    }

    /**
     * Generate WhatsApp payment reminder message.
     */
    public function reminderText(float $balance, ShopSetting $shop): string
    {
        return sprintf(
            'Assalam o Alaikum %s, %s mein aap ka %s baqi hai. Meherbani karke jald ada karein. Shukriya.',
            $this->name,
            $shop->shop_name ?: 'our shop',
            $shop->money($balance),
        );
    }
}
