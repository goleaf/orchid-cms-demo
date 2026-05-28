<?php

namespace Database\Factories;

use App\Models\CommunicationThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunicationThread>
 */
class CommunicationThreadFactory extends Factory
{
    protected $model = CommunicationThread::class;

    public function definition(): array
    {
        return [
            'thread_number' => 'THR-FOUNDATION-'.$this->faker->unique()->numerify('####'),
            'subject' => $this->faker->sentence(5),
            'target_type' => User::class,
            'target_id' => User::factory(),
            'student_id' => null,
            'lead_id' => null,
            'status' => CommunicationThread::STATUS_OPEN,
            'metadata' => null,
        ];
    }
}
