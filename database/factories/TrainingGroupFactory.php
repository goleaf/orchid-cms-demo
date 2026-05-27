<?php

namespace Database\Factories;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingGroup>
 */
class TrainingGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'group_number' => strtoupper($this->faker->unique()->bothify('GROUP-####')),
            'branch_id' => Branch::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'course_category_id' => null,
            'instructor_id' => Instructor::factory(),
            'name' => 'Group '.$this->faker->unique()->bothify('B-##'),
            'description_translations' => null,
            'schedule_summary_translations' => null,
            'code' => strtoupper($this->faker->unique()->bothify('GRP-####')),
            'status' => GroupStatus::Recruiting,
            'capacity' => $this->faker->numberBetween(8, 16),
            'places_taken' => $this->faker->numberBetween(0, 4),
            'starts_on' => now()->addDays($this->faker->numberBetween(7, 30)),
            'ends_on' => now()->addMonths($this->faker->numberBetween(3, 6)),
            'meeting_days' => ['monday', 'wednesday'],
            'meeting_time' => '18:00',
            'end_time' => '20:00',
            'classroom' => 'Room '.$this->faker->numberBetween(1, 6),
            'is_visible_on_site' => true,
            'is_featured' => false,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function publicVisible(): static
    {
        return $this->state(fn (): array => [
            'status' => GroupStatus::Recruiting,
            'is_visible_on_site' => true,
        ]);
    }
}
