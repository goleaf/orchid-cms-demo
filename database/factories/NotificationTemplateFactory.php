<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $code = 'template_'.$this->faker->unique()->bothify('####');
        $name = str($code)->replace('_', ' ')->title()->toString();

        return [
            'code' => $code,
            'channel_id' => null,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(6)),
            'template_group' => $this->faker->randomElement(['general', 'student', 'lead', 'reminder', 'internal']),
            'is_active' => true,
            'is_system' => false,
        ];
    }

    public function forChannel(NotificationChannel $channel): static
    {
        return $this->state(fn (): array => [
            'channel_id' => $channel->id,
        ]);
    }

    public function appointmentReminder(): static
    {
        return $this->templateState('appointment_reminder', 'Appointment reminder', 'lesson');
    }

    public function paymentReminder(): static
    {
        return $this->templateState('payment_due', 'Payment due reminder', 'finance');
    }

    public function documentRejected(): static
    {
        return $this->templateState('document_rejected', 'Document rejected', 'documents');
    }

    public function lessonReminder(): static
    {
        return $this->templateState('lesson_reminder', 'Lesson reminder', 'lesson');
    }

    public function examReminder(): static
    {
        return $this->templateState('exam_reminder', 'Exam reminder', 'exams');
    }

    public function leadFollowUp(): static
    {
        return $this->templateState('lead_follow_up', 'Lead follow-up', 'lead');
    }

    public function studentWelcome(): static
    {
        return $this->templateState('student_welcome', 'Student welcome', 'student');
    }

    public function contractGenerated(): static
    {
        return $this->templateState('contract_generated', 'Contract generated', 'documents');
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'is_system' => true,
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => [
                'ru' => 'Переведенный шаблон',
                'en' => 'Translated template',
                'lt' => 'Isverstas sablonas',
                'pl' => 'Przetlumaczony szablon',
            ],
            'description_translations' => [
                'ru' => 'Тестовое описание шаблона.',
                'en' => 'Test template description.',
                'lt' => 'Bandomasis sablono aprasymas.',
                'pl' => 'Testowy opis szablonu.',
            ],
        ]);
    }

    private function templateState(string $code, string $name, string $group): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($name.' notification template.'),
            'template_group' => $group,
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
