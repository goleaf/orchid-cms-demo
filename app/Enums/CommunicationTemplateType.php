<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CommunicationTemplateType: string
{
    use HasEnumValues;

    case General = 'general';
    case Internal = 'internal';
    case Student = 'student';
    case Lead = 'lead';
    case Reminder = 'reminder';

    public function label(): string
    {
        return tkey('communication.templates.types.'.$this->value);
    }
}
