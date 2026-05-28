<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum NotificationMessageStatus: string
{
    use HasEnumValues;

    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function label(): string
    {
        return tkey('notifications.messages.statuses.'.$this->value);
    }
}
