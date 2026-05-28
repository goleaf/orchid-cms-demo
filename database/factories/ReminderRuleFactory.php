<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use App\Models\ReminderRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReminderRule>
 */
class ReminderRuleFactory extends Factory
{
    protected $model = ReminderRule::class;

    public function definition(): array
    {
        $code = 'reminder_'.$this->faker->unique()->bothify('####');

        return [
            'code' => $code,
            'name_translations' => $this->translations(str($code)->replace('_', ' ')->title()->toString()),
            'trigger_type' => $this->faker->randomElement(['before_lesson', 'after_signup', 'before_payment_due']),
            'target_type' => $this->faker->randomElement(['student', 'lead', 'lesson', 'enrollment']),
            'template_id' => NotificationTemplate::factory(),
            'offset_minutes' => 60,
            'is_active' => true,
            'metadata' => null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
