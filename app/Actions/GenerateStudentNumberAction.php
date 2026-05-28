<?php

namespace App\Actions;

use App\Models\Student;

class GenerateStudentNumberAction
{
    public function handle(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        $prefix = 'STU-'.$year.'-';
        $next = $this->nextSequence($prefix);

        do {
            $number = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Student::query()->where('student_number', $number)->exists());

        return $number;
    }

    private function nextSequence(string $prefix): int
    {
        $latest = Student::query()
            ->where('student_number', 'like', $prefix.'%')
            ->orderByDesc('student_number')
            ->value('student_number');

        if (! is_string($latest) || ! str_starts_with($latest, $prefix)) {
            return 1;
        }

        return ((int) str($latest)->afterLast('-')->toString()) + 1;
    }
}
