<?php

namespace App\Enums;

enum ExamChecklistItemStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Failed = 'failed';
    case Waived = 'waived';

    public function clearsAdmission(): bool
    {
        return in_array($this, [
            self::Passed,
            self::Waived,
        ], true);
    }
}
