<?php

namespace App\Support;

/**
 * Money maths without floats. Amounts are decimal(12,2) in the database, so every value is
 * turned into whole paisa (an int) before it is added, compared or subtracted.
 */
final class Cents
{
    /** "1234.56" / 1234.56 / null  ->  123456 */
    public static function from(string|int|float|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $s = is_float($value) ? number_format($value, 3, '.', '') : trim((string) $value);

        $negative = str_starts_with($s, '-');
        $s        = ltrim($s, '+-');

        [$whole, $fraction] = array_pad(explode('.', $s, 2), 2, '0');

        $fraction = str_pad($fraction, 3, '0');
        $cents    = ((int) $whole) * 100 + (int) substr($fraction, 0, 2);

        if ((int) $fraction[2] >= 5) {   // round half up on the third decimal
            $cents++;
        }

        return $negative ? -$cents : $cents;
    }

    /** 123456 -> "1234.56" (plain, for CSV and inputs) */
    public static function plain(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs  = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($abs, 100), $abs % 100);
    }

    /** 123456 -> "1,234.56" (for screens) */
    public static function format(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs  = abs($cents);

        return sprintf('%s%s.%02d', $sign, number_format(intdiv($abs, 100)), $abs % 100);
    }
}
