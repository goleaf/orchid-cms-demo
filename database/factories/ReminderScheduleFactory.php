<?php

namespace Database\Factories;

use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReminderSchedule>
 */
class ReminderScheduleFactory extends Factory
{
    protected $model = ReminderSchedule::class;

    public function definition(): array
    {
        return [
            'rule_id' => ReminderRule::factory(),
            'target_type' => User::class,
            'target_id' => User::factory(),
            'message_id' => null,
            'scheduled_at' => now()->addHour(),
            'status' => ReminderSchedule::STATUS_SCHEDULED,
            'processed_at' => null,
            'metadata' => null,
        ];
    }
}
