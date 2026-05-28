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

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
