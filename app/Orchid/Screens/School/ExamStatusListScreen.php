<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamStatus;

class ExamStatusListScreen extends ExamDictionaryListScreen
{
    protected function modelClass(): string
    {
        return ExamStatus::class;
    }

    protected function titleKey(): string
    {
        return 'exams.dictionaries.statuses.title';
    }
}
