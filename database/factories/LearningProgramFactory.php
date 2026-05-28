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

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => $this->translations('Стандартная программа категории B', 'Category B Standard Program', 'B kategorijos standartine programa', 'Program standardowy kategorii B'),
            'description_translations' => $this->translations('Полная программа обучения категории B.', 'Full Category B training program.', 'Pilna B kategorijos mokymo programa.', 'Pelny program szkolenia kategorii B.'),
        ]);
    }

    public function forCategoryB(): static
    {
        return $this->state(fn (): array => [
            'course_id' => Course::factory()->categoryB(),
            'course_category_id' => CourseCategory::factory()->categoryB(),
            'code' => 'category_b_standard',
            'name_translations' => $this->translations('Категория B стандарт', 'Category B Standard Program', 'B kategorijos standartine programa', 'Program standardowy kategorii B'),
        ]);
    }

    public function forCategoryA(): static
    {
        return $this->state(fn (): array => [
            'course_id' => Course::factory()->categoryA(),
            'course_category_id' => CourseCategory::factory()->categoryA(),
            'code' => 'category_a_standard',
            'name_translations' => $this->translations('Категория A стандарт', 'Category A Standard Program', 'A kategorijos standartine programa', 'Program standardowy kategorii A'),
        ]);
    }

    public function forIndividualLessons(): static
    {
        return $this->state(fn (): array => [
            'course_id' => Course::factory()->individualLessons(),
            'course_category_id' => CourseCategory::factory()->individualLessons(),
            'code' => 'individual_lessons',
            'name_translations' => $this->translations('Индивидуальные уроки', 'Individual Driving Lessons Program', 'Individualiu vairavimo pamoku programa', 'Program indywidualnych lekcji jazdy'),
        ]);
    }

    public function forExamPreparation(): static
    {
        return $this->state(fn (): array => [
            'course_id' => Course::factory()->examPreparation(),
            'course_category_id' => CourseCategory::factory()->examPreparation(),
            'code' => 'exam_preparation',
            'name_translations' => $this->translations('Подготовка к экзамену', 'Exam Preparation Program', 'Pasiruosimo egzaminui programa', 'Program przygotowania do egzaminu'),
        ]);
    }

    public function standard(): static
    {
        return $this->state(fn (): array => [
            'code' => 'standard_program',
            'name_translations' => $this->translations('Стандартная программа', 'Standard Program', 'Standartine programa', 'Program standardowy'),
            'sort_order' => 10,
        ]);
    }

    public function intensive(): static
    {
        return $this->state(fn (): array => [
            'code' => 'intensive_program',
            'name_translations' => $this->translations('Интенсивная программа', 'Intensive Program', 'Intensyvi programa', 'Program intensywny'),
            'sort_order' => 20,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
