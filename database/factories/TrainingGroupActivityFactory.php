<?php

namespace Database\Factories;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingGroupActivity>
 */
class TrainingGroupActivityFactory extends Factory
{
    protected $model = TrainingGroupActivity::class;

    public function definition(): array
    {
        return [
            'training_group_id' => TrainingGroup::factory(),
            'enrollment_id' => null,
            'membership_id' => null,
            'student_profile_id' => null,
            'user_id' => null,
            'type' => 'updated',
            'title' => null,
            'body' => null,
            'old_value' => null,
            'new_value' => null,
            'meta' => null,
        ];
    }

    public function studentAdded(): static
    {
        return $this->state(fn (): array => ['type' => 'student_added']);
    }

    public function schedulePatternSaved(): static
    {
        return $this->state(fn (): array => ['type' => 'schedule_pattern_saved']);
    }
}
