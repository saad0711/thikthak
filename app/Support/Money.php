<?php

namespace App\Support;

/**
 * Formats amounts in Bangladeshi Taka for display.
 */
class Money
{
    public static function bdt(int|float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return '৳'.number_format((float) $amount);
    }

    public static function range(int|float|string|null $min, int|float|string|null $max): string
    {
        if ($min === null && $max === null) {
            return 'Price on request';
        }

        if ((float) $min === (float) $max) {
            return self::bdt($min);
        }

        return self::bdt($min).' – '.self::bdt($max);
    }
}
