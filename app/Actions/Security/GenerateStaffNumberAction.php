<?php

namespace App\Actions\Security;

use App\Models\StaffProfile;

class GenerateStaffNumberAction
{
    public function handle(): string
    {
        $prefix = 'STAFF-'.now()->format('Y').'-';
        $lastNumber = StaffProfile::query()
            ->where('staff_number', 'like', $prefix.'%')
            ->orderByDesc('staff_number')
            ->value('staff_number');

        $next = $this->nextSequence($lastNumber, $prefix);

        do {
            $staffNumber = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (StaffProfile::query()->where('staff_number', $staffNumber)->exists());

        return $staffNumber;
    }

    private function nextSequence(?string $lastNumber, string $prefix): int
    {
        if (! is_string($lastNumber) || ! str_starts_with($lastNumber, $prefix)) {
            return 1;
        }

        $suffix = substr($lastNumber, strlen($prefix));

        return max(1, ((int) $suffix) + 1);
    }
}
