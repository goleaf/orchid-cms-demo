<?php

namespace App\Support\Crm;

class PhoneNormalizer
{
    public static function normalize(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (! filled($digits)) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '8') && strlen($digits) === 9) {
            $digits = '370'.substr($digits, 1);
        }

        return '+'.$digits;
    }

    public static function searchToken(?string $phone): ?string
    {
        $normalized = static::normalize($phone);

        return filled($normalized)
            ? preg_replace('/\D+/', '', $normalized)
            : null;
    }
}
