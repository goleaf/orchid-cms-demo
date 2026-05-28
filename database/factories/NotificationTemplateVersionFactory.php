<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplateVersion>
 */
class NotificationTemplateVersionFactory extends Factory
{
    protected $model = NotificationTemplateVersion::class;

    public function definition(): array
    {
        return [
            'template_id' => NotificationTemplate::factory(),
            'version' => 1,
            'status' => NotificationTemplateVersion::STATUS_DRAFT,
            'subject_translations' => $this->translations('Lesson reminder'),
            'body_translations' => $this->translations('Your lesson starts at {{ lesson_time }}.'),
            'variables_schema' => [
                'lesson_time' => ['type' => 'string', 'required' => true],
            ],
            'published_at' => null,
            'published_by_id' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationTemplateVersion::STATUS_PUBLISHED,
            'published_at' => now(),
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
