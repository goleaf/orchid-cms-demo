<?php

namespace App\Actions;

use App\Models\TrainingGroup;

class GenerateTrainingGroupNumberAction
{
    public function handle(?int $year = null): string
    {
        $year ??= (int) now()->year;
        $prefix = 'GRP-'.$year.'-';
        $next = 1;

        do {
            $number = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (TrainingGroup::query()->where('group_number', $number)->exists());

        return $number;
    }
}
