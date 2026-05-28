<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamAttemptStatus;

class ExamAttemptStatusListScreen extends ExamDictionaryListScreen
{
    protected function modelClass(): string
    {
        return ExamAttemptStatus::class;
    }

    protected function titleKey(): string
    {
        return 'exams.dictionaries.attempt_statuses.title';
    }
}
