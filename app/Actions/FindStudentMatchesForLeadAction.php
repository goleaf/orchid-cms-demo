<?php

namespace App\Actions;

use App\Models\MarketingLead;
use Illuminate\Support\Collection;

class FindStudentMatchesForLeadAction
{
    /**
     * @return Collection<int, array{student: \App\Models\Student, reason: string}>
     */
    public function handle(MarketingLead $lead): Collection
    {
        return app(FindMatchingStudentsAction::class)
            ->handle([
                'phone' => $lead->phone,
                'normalized_phone' => $lead->normalized_phone,
                'email' => $lead->email,
                'full_name' => $lead->fullName(),
            ])
            ->map(fn (array $match): array => [
                'student' => $match['student'],
                'reason' => match ($match['reason']) {
                    'phone' => 'phone_match',
                    'email' => 'email_match',
                    'personal_code' => 'personal_code_match',
                    default => 'name_match',
                },
            ])
            ->values();
    }
}
