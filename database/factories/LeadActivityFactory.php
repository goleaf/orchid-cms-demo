<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadActivity>
 */
class LeadActivityFactory extends Factory
{
    protected $model = LeadActivity::class;

    public function definition(): array
    {
        return [
            'marketing_lead_id' => Lead::factory(),
            'user_id' => null,
            'type' => 'created',
            'title' => tkey('crm.activities.titles.created'),
            'body' => $this->faker->sentence(),
            'old_value' => null,
            'new_value' => null,
            'meta' => null,
        ];
    }

    public function created(): static
    {
        return $this->typed('created');
    }

    public function createdFromWebsite(): static
    {
        return $this->typed('created_from_website');
    }

    public function createdManually(): static
    {
        return $this->typed('created_manually');
    }

    public function statusChanged(): static
    {
        return $this->typed('status_changed', [
            'old_value' => 'new',
            'new_value' => 'contacted',
        ]);
    }

    public function managerAssigned(): static
    {
        return $this->typed('manager_assigned');
    }

    public function noteAdded(): static
    {
        return $this->typed('note_added', [
            'body' => $this->faker->paragraph(),
        ]);
    }

    public function callLogged(): static
    {
        return $this->typed('call_logged', [
            'meta' => [
                'result' => 'reached',
                'duration_seconds' => 180,
            ],
        ]);
    }

    public function taskCreated(): static
    {
        return $this->typed('task_created');
    }

    public function taskCompleted(): static
    {
        return $this->typed('task_completed');
    }

    public function markedDuplicate(): static
    {
        return $this->typed('marked_duplicate');
    }

    public function markedLost(): static
    {
        return $this->typed('marked_lost');
    }

    public function markedSpam(): static
    {
        return $this->typed('marked_spam');
    }

    public function reopened(): static
    {
        return $this->typed('reopened');
    }

    public function converted(): static
    {
        return $this->typed('converted');
    }

    public function archived(): static
    {
        return $this->typed('archived');
    }

    public function updated(): static
    {
        return $this->typed('updated');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function typed(string $type, array $overrides = []): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
            'title' => tkey('crm.activities.titles.'.$type),
            ...$overrides,
        ]);
    }
}
