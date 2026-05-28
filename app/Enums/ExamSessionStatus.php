<?php

namespace App\Enums;

enum ExamSessionStatus: string
{
    case Planned = 'planned';
    case Open = 'open';
    case Full = 'full';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function acceptsAttempts(): bool
    {
        return in_array($this, [
            self::Planned,
            self::Open,
        ], true);
    }
}
