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
            'trigger_type' => $this->faker->randomElement(ReminderRule::triggerValues()),
            'target_type' => $this->faker->randomElement(['student', 'lead', 'lesson', 'enrollment']),
            'template_id' => NotificationTemplate::factory(),
            'offset_minutes' => 60,
            'is_active' => true,
            'metadata' => null,
        ];
    }

    public function lessonTomorrow(): static
    {
        return $this->reminderState(
            'lesson_tomorrow',
            'Lesson tomorrow',
            ReminderRule::TRIGGER_BEFORE_LESSON,
            'student',
            -1440,
        );
    }

    public function lessonOneHourBefore(): static
    {
        return $this->reminderState(
            'lesson_one_hour_before',
            'Lesson one hour before',
            ReminderRule::TRIGGER_BEFORE_LESSON,
            'student',
            -60,
        );
    }

    public function paymentDue(): static
    {
        return $this->reminderState(
            'payment_due',
            'Payment due',
            ReminderRule::TRIGGER_BEFORE_PAYMENT_DUE,
            'student',
            -1440,
        );
    }

    public function documentMissing(): static
    {
        return $this->reminderState(
            'document_missing',
            'Document missing',
            ReminderRule::TRIGGER_MANUAL,
            'student',
            0,
        );
    }

    public function examReminder(): static
    {
        return $this->reminderState(
            'exam_reminder',
            'Exam reminder',
            ReminderRule::TRIGGER_BEFORE_EXAM,
            'student',
            -1440,
        );
    }

    public function leadFollowUp(): static
    {
        return $this->reminderState(
            'lead_follow_up',
            'Lead follow-up',
            ReminderRule::TRIGGER_AFTER_SIGNUP,
            'lead',
            1440,
        );
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
        ]);
    }

    private function reminderState(
        string $code,
        string $name,
        string $triggerType,
        string $targetType,
        int $offsetMinutes,
    ): static {
        return $this->state(fn (): array => [
            'code' => $code,
            'name_translations' => $this->translations($name),
            'trigger_type' => $triggerType,
            'target_type' => $targetType,
            'offset_minutes' => $offsetMinutes,
            'is_active' => true,
        ]);
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
