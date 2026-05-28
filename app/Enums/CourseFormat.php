<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CourseFormat: string
{
    use HasEnumValues;

    case Offline = 'offline';
    case Online = 'online';
    case Hybrid = 'hybrid';
    case Individual = 'individual';
    case Group = 'group';
    case Mixed = 'mixed';

    public function label(): string
    {
        return tkey('website.courses.formats.'.$this->value);
    }
}
