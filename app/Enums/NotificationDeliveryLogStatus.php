<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum NotificationDeliveryLogStatus: string
{
    use HasEnumValues;

    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Read = 'read';

    public function label(): string
    {
        return tkey('communication.delivery_logs.statuses.'.$this->value);
    }
}
