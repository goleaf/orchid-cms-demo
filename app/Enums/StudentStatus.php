<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
    case Lead = 'lead';
    case Enrolled = 'enrolled';
    case Graduated = 'graduated';
    case Archived = 'archived';

    public function isActiveWorkflow(): bool
    {
        return in_array($this, [
            self::Active,
            self::Lead,
            self::Enrolled,
        ], true);
    }

    public function isBlocked(): bool
    {
        return $this === self::Blocked;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}
