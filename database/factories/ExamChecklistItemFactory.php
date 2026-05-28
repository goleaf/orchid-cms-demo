<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\ExamChecklistItem;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamChecklistItem>
 */
class ExamChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        $key = $this->faker->randomElement(['identity_document', 'no_debt', 'theory_hours', 'practice_hours']);
        $title = str($key)->replace('_', ' ')->title()->toString();

        return [
            'exam_session_id' => ExamSession::factory(),
            'attempt_id' => null,
            'student_id' => Student::factory(),
            'enrollment_id' => StudentEnrollment::factory(),
            'key' => $key,
            'title_translations' => [
                'ru' => $title,
                'en' => $title,
                'lt' => $title,
                'pl' => $title,
            ],
            'status' => 'pending',
            'required' => true,
        ];
    }

    public function forAttempt(ExamAttempt $attempt): static
    {
        return $this->state(fn (): array => [
            'exam_session_id' => $attempt->exam_session_id,
            'attempt_id' => $attempt->id,
            'student_id' => $attempt->student_profile_id,
            'enrollment_id' => $attempt->enrollment_id,
        ]);
    }

    public function passed(): static
    {
        return $this->state(fn (): array => ['status' => 'passed']);
    }
}
