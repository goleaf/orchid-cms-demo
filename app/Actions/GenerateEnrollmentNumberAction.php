<?php

namespace App\Actions;

use App\Models\StudentEnrollment;

class GenerateEnrollmentNumberAction
{
    public function handle(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        $prefix = 'ENR-'.$year.'-';
        $next = $this->nextSequence($prefix);

        do {
            $number = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (StudentEnrollment::query()->where('enrollment_number', $number)->exists());

        return $number;
    }

    private function nextSequence(string $prefix): int
    {
        $latest = StudentEnrollment::query()
            ->where('enrollment_number', 'like', $prefix.'%')
            ->orderByDesc('enrollment_number')
            ->value('enrollment_number');

        if (! is_string($latest) || ! str_starts_with($latest, $prefix)) {
            return 1;
        }

        return ((int) str($latest)->afterLast('-')->toString()) + 1;
    }
}
