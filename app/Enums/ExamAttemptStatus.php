<?php

namespace App\Enums;

enum ExamAttemptStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Passed = 'passed';
    case Failed = 'failed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    public function isCompleted(): bool
    {
        return in_array($this, [
            self::Passed,
            self::Failed,
            self::NoShow,
            self::Cancelled,
        ], true);
    }

    public function canBeRetaken(): bool
    {
        return in_array($this, [
            self::Failed,
            self::NoShow,
            self::Cancelled,
        ], true);
    }
}
