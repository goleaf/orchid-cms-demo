<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingGroupSchedulePattern>
 */
class TrainingGroupSchedulePatternFactory extends Factory
{
    protected $model = TrainingGroupSchedulePattern::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'training_group_id' => TrainingGroup::factory(),
            'title_translations' => $this->translations('Теория вечером', 'Evening theory', 'Vakaro teorija', 'Teoria wieczorem'),
            'day_of_week' => $this->faker->numberBetween(1, 7),
            'starts_at' => '18:00',
            'ends_at' => '20:00',
            'lesson_type' => 'theory',
            'classroom' => 'Room '.$this->faker->numberBetween(1, 5),
            'instructor_id' => Instructor::factory(),
            'is_active' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['lesson_type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['lesson_type' => 'practice']);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
