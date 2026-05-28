<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningTopic>
 */
class LearningTopicFactory extends Factory
{
    protected $model = LearningTopic::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'training_program_id' => Course::factory(),
            'course_module_id' => null,
            'learning_program_module_id' => LearningProgramModule::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('TOPIC-####')),
            'name_translations' => $this->translations('Тема обучения', 'Learning topic', 'Mokymo tema', 'Temat nauki'),
            'title_translations' => $this->translations('Тема обучения', 'Learning topic', 'Mokymo tema', 'Temat nauki'),
            'description_translations' => null,
            'topic_type' => 'theory',
            'duration_minutes' => 45,
            'estimated_hours' => 1.00,
            'sort_order' => 0,
            'is_required' => true,
            'is_active' => true,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function forModule(): static
    {
        return $this->state(fn (): array => ['learning_program_module_id' => LearningProgramModule::factory()]);
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['topic_type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['topic_type' => 'practice']);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
