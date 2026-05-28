<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum GearboxType: string
{
    use HasEnumValues;

    case Manual = 'manual';
    case Automatic = 'automatic';
    case Both = 'both';
    case Unknown = 'unknown';

    public function label(): string
    {
        return tkey('students.gearbox.'.$this->value);
    }
}
