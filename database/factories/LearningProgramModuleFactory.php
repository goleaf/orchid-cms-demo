<?php

namespace Database\Factories;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningProgramModule>
 */
class LearningProgramModuleFactory extends Factory
{
    protected $model = LearningProgramModule::class;

    public function definition(): array
    {
        return [
            'learning_program_id' => LearningProgram::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('LPM-####')),
            'type' => 'theory',
            'name_translations' => $this->translations('Теоретический модуль', 'Theory module', 'Teorijos modulis', 'Modul teorii'),
            'description_translations' => null,
            'required_hours' => 10,
            'sort_order' => 0,
            'is_required' => true,
            'is_active' => true,
        ];
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['type' => 'practice']);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => ['type' => 'exam_preparation']);
    }

    public function internalExam(): static
    {
        return $this->state(fn (): array => ['type' => 'internal_exam']);
    }

    public function stateExamPreparation(): static
    {
        return $this->state(fn (): array => ['type' => 'state_exam_preparation']);
    }

    public function documents(): static
    {
        return $this->state(fn (): array => ['type' => 'documents']);
    }

    public function onboarding(): static
    {
        return $this->state(fn (): array => ['type' => 'onboarding']);
    }

    public function other(): static
    {
        return $this->state(fn (): array => ['type' => 'other']);
    }

    public function required(): static
    {
        return $this->state(fn (): array => ['is_required' => true]);
    }

    public function optional(): static
    {
        return $this->state(fn (): array => ['is_required' => false]);
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
            'name_translations' => $this->translations('Модуль программы', 'Learning program module', 'Mokymo programos modulis', 'Modul programu nauki'),
            'description_translations' => $this->translations('Структурный модуль программы обучения.', 'A structured module of the learning program.', 'Strukturinis mokymo programos modulis.', 'Strukturalny modul programu nauki.'),
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
