<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum NotificationTemplateVersionStatus: string
{
    use HasEnumValues;

    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return tkey('notifications.template_versions.statuses.'.$this->value);
    }
}
