<?php

namespace App\Enums;

enum ExamStatus: string
{
    case Scheduled = 'scheduled';
    case Passed = 'passed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
