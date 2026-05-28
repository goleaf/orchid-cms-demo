<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningProgram>
 */
class LearningProgramFactory extends Factory
{
    protected $model = LearningProgram::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'course_id' => Course::factory(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('LP-####')),
            'name_translations' => $this->translations('Стандартная программа', 'Standard program', 'Standartine programa', 'Program standardowy'),
            'description_translations' => $this->translations('Базовая программа обучения.', 'Basic learning program.', 'Bazine mokymo programa.', 'Podstawowy program nauki.'),
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
