<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum TrainingGroupMembershipStatus: string
{
    use HasEnumValues;

    case Invited = 'invited';
    case Pending = 'pending';
    case Active = 'active';
    case Left = 'left';
    case Waitlisted = 'waitlisted';
    case Transferred = 'transferred';
    case Completed = 'completed';
    case Removed = 'removed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('education.groups.memberships.statuses.'.$this->value);
    }
}
