<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token', 'recovery_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin','manager','cashier','stock'];
     
    public function hasRole(string ...$role): bool
    {
        return in_array($this->role,$role, true);
    }

    public static function normalizeCode(string $code): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
}

public static function makeRecoveryCode(): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // Excludes 0/O and 1/I to avoid reading errors.
    $code = '';
    for ($i = 0; $i < 12; $i++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return implode('-', str_split($code, 4));
}

// Generates a new recovery code, hashes and saves it, then returns the original code.
public function issueRecoveryCode(): string
{
    $code = self::makeRecoveryCode();
    $this->forceFill(['recovery_code' => Hash::make(self::normalizeCode($code))])->save();
    return $code;
}

public function checkRecoveryCode(string $code): bool
{
    return $this->recovery_code
        && Hash::check(self::normalizeCode($code), $this->recovery_code);
}
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
