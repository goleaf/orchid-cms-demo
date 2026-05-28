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

    public function created(): static
    {
        return $this->activity('created');
    }

    public function updated(): static
    {
        return $this->activity('updated');
    }

    public function archived(): static
    {
        return $this->activity('archived');
    }

    public function statusChanged(): static
    {
        return $this->state(fn (): array => [
            'type' => 'status_changed',
            'old_value' => 'draft',
            'new_value' => 'recruiting',
        ]);
    }

    public function studentRemoved(): static
    {
        return $this->activity('student_removed');
    }

    public function studentWaitlisted(): static
    {
        return $this->activity('student_waitlisted');
    }

    public function studentTransferredIn(): static
    {
        return $this->activity('student_transferred_in');
    }

    public function studentTransferredOut(): static
    {
        return $this->activity('student_transferred_out');
    }

    public function membershipCompleted(): static
    {
        return $this->activity('membership_completed');
    }

    public function schedulePatternCreated(): static
    {
        return $this->activity('schedule_pattern_created');
    }

    public function schedulePatternUpdated(): static
    {
        return $this->activity('schedule_pattern_updated');
    }

    public function schedulePatternDeleted(): static
    {
        return $this->activity('schedule_pattern_deleted');
    }

    public function capacityChanged(): static
    {
        return $this->state(fn (): array => [
            'type' => 'capacity_changed',
            'old_value' => '1',
            'new_value' => '2',
        ]);
    }

    public function publishedOnSite(): static
    {
        return $this->activity('published_on_site');
    }

    public function hiddenFromSite(): static
    {
        return $this->activity('hidden_from_site');
    }

    public function completed(): static
    {
        return $this->activity('completed');
    }

    public function cancelled(): static
    {
        return $this->activity('cancelled');
    }

    public function noteAdded(): static
    {
        return $this->state(fn (): array => [
            'type' => 'note_added',
            'body' => 'Training group note.',
        ]);
    }

    public function learningProgramAssigned(): static
    {
        return $this->activity('learning_program_assigned');
    }

    public function teacherAssigned(): static
    {
        return $this->activity('teacher_assigned');
    }

    public function managerAssigned(): static
    {
        return $this->activity('manager_assigned');
    }

    private function activity(string $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }
}
