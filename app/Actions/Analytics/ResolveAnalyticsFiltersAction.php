<?php

namespace App\Actions\Analytics;

use App\Models\User;

class ResolveAnalyticsFiltersAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters = [], ?User $user = null): array
    {
        $range = app(BuildAnalyticsDateRangeAction::class)->handle($filters);

        $resolved = [
            'period_type' => $range['period_type'],
            'period_start' => $range['period_start']->toDateString(),
            'period_end' => $range['period_end']->toDateString(),
        ];

        foreach ([
            'branch_id',
            'user_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'manager_id',
            'responsible_manager_id',
        ] as $key) {
            if (filled($filters[$key] ?? null)) {
                $resolved[$key] = (int) $filters[$key];
            }
        }

        foreach (['status', 'source', 'report_group', 'widget_type'] as $key) {
            if (filled($filters[$key] ?? null) && is_scalar($filters[$key])) {
                $resolved[$key] = (string) $filters[$key];
            }
        }

        if ($user !== null && ! isset($resolved['requested_by_id'])) {
            $resolved['requested_by_id'] = $user->id;
        }

        return $resolved;
    }
}
