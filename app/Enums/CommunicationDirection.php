<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CommunicationDirection: string
{
    use HasEnumValues;

    case Inbound = 'inbound';
    case Outbound = 'outbound';
    case Internal = 'internal';

    public function label(): string
    {
        return tkey('communication.messages.directions.'.$this->value);
    }
}
