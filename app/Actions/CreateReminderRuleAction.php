<?php

namespace App\Actions;

use App\Models\ReminderRule;

class CreateReminderRuleAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ReminderRule
    {
        return ReminderRule::query()->updateOrCreate(
            ['code' => $data['code']],
            [
                'name_translations' => $data['name_translations'] ?? null,
                'trigger_type' => $data['trigger_type'],
                'target_type' => $data['target_type'],
                'template_id' => $data['template_id'] ?? null,
                'offset_minutes' => $data['offset_minutes'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'metadata' => $data['metadata'] ?? null,
            ],
        )->refresh();
    }
}
