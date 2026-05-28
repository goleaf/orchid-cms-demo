<?php

namespace App\Support;

use BackedEnum;
use Illuminate\Support\Str;

class LocalizedLabel
{
    public static function for(string $prefix, BackedEnum|string|null $value, string $empty = '-'): string
    {
        if ($value === null || $value === '') {
            return $empty;
        }

        $rawValue = $value instanceof BackedEnum ? (string) $value->value : (string) $value;
        $key = $prefix.'.'.$rawValue;
        $label = tkey($key);

        if ($label !== $key) {
            return $label;
        }

        return Str::of($rawValue)->replace('_', ' ')->title()->toString();
    }
}
