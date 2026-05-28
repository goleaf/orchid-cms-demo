<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Models\ExamResultStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamResult>
 */
class ExamResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attempt_id' => ExamAttempt::factory(),
            'result_status_id' => ExamResultStatus::factory()->pending(),
            'score' => null,
            'max_score' => 100,
            'passed' => false,
            'examiner_comment' => null,
            'mistakes_summary' => null,
            'decided_by_id' => User::factory(),
            'decided_at' => now(),
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (): array => [
            'result_status_id' => ExamResultStatus::factory()->passed(),
            'score' => 90,
            'max_score' => 100,
            'passed' => true,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'result_status_id' => ExamResultStatus::factory()->failed(),
            'score' => 45,
            'max_score' => 100,
            'passed' => false,
        ]);
    }
}
