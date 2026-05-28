<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CommunicationThreadStatus: string
{
    use HasEnumValues;

    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return tkey('communication.threads.statuses.'.$this->value);
    }
}
