<?php

namespace App\Actions;

use App\Support\Notifications\NotificationTargetResolver;
use Illuminate\Database\Eloquent\Model;

class ResolveNotificationTemplateVariablesAction
{
    /**
     * @param  array<string, scalar|null>  $variables
     * @return array<string, scalar|null>
     */
    public function handle(?Model $target = null, array $variables = []): array
    {
        $resolved = [
            'current_date' => now()->toDateString(),
            'current_time' => now()->format('H:i'),
        ];

        if ($target !== null) {
            $resolved = [
                ...$resolved,
                ...app(NotificationTargetResolver::class)->variables($target),
            ];
        }

        return [
            ...$resolved,
            ...$variables,
        ];
    }
}
