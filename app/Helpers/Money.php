<?php

namespace App\Helpers;

class Money
{
    /**
     * Convert decimal string/number to integer cents.
     */
    public static function toCents($amount): int
    {
        $str = is_string($amount) ? trim($amount) : (string) $amount;
        if ($str === '') {
            return 0;
        }

        // Normalize like "500" -> "500.00"
        if (! str_contains($str, '.')) {
            return (int) $str * 100;
        }

        [$whole, $frac] = explode('.', $str, 2);
        $whole = $whole === '' ? '0' : $whole;
        $frac = preg_replace('/\D/', '', $frac);
        $frac = substr(str_pad($frac, 2, '0'), 0, 2);

        $sign = 1;
        if (str_starts_with($whole, '-')) {
            $sign = -1;
            $whole = ltrim($whole, '-');
        }

        return $sign * ((int) $whole * 100 + (int) $frac);
    }

    public static function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $cents = abs($cents);
        $whole = intdiv($cents, 100);
        $frac = $cents % 100;
        return sprintf('%s%d.%02d', $sign, $whole, $frac);
    }

    public static function add($a, $b): string
    {
        return self::fromCents(self::toCents($a) + self::toCents($b));
    }

    public static function mul($amount, int $qty): string
    {
        return self::fromCents(self::toCents($amount) * $qty);
    }

    /**
     * Compare two amounts.
     * Returns -1 if a<b, 0 if a==b, 1 if a>b
     */
    public static function cmp($a, $b): int
    {
        return self::toCents($a) <=> self::toCents($b);
    }
}

