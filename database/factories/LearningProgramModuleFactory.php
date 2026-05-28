<?php

namespace Database\Factories;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use Illuminate\Support\Str;

class LearningProgramModuleFactory extends CourseModuleFactory
{
    protected $model = LearningProgramModule::class;

    public function definition(): array
    {
        return [
            ...parent::definition(),
            'uuid' => (string) Str::uuid(),
            'training_program_id' => LearningProgram::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('MOD-####')),
            'title_translations' => $this->translations('Модуль обучения', 'Learning module', 'Mokymo modulis', 'Modul nauki'),
            'description_translations' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['module_type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['module_type' => 'practice']);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
