<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamType;

class ExamTypeListScreen extends ExamDictionaryListScreen
{
    protected function modelClass(): string
    {
        return ExamType::class;
    }

    protected function titleKey(): string
    {
        return 'exams.dictionaries.types.title';
    }
}
