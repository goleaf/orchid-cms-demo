<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamResultStatus;

class ExamResultStatusListScreen extends ExamDictionaryListScreen
{
    protected function modelClass(): string
    {
        return ExamResultStatus::class;
    }

    protected function titleKey(): string
    {
        return 'exams.dictionaries.result_statuses.title';
    }
}
