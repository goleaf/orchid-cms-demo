<?php

namespace App\Enums;

enum ExamAdmissionStatus: string
{
    case Draft = 'draft';
    case Checking = 'checking';
    case Ready = 'ready';
    case Admitted = 'admitted';
    case Blocked = 'blocked';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Passed = 'passed';
    case Failed = 'failed';
    case RetakeRequired = 'retake_required';
    case RetakeScheduled = 'retake_scheduled';

    public function allowsAttempt(): bool
    {
        return in_array($this, [
            self::Ready,
            self::Admitted,
            self::RetakeScheduled,
        ], true);
    }
}
