<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum TransmissionType: string
{
    use HasEnumValues;

    case Manual = 'manual';
    case Automatic = 'automatic';

    public function label(): string
    {
        return tkey('website.transmissions.'.$this->value);
    }
}
