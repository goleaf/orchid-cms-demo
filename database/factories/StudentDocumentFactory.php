<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Enrollment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDocument>
 */
class StudentDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'enrollment_id' => Enrollment::factory(),
            'document_type' => $this->faker->randomElement(['id_card', 'medical_certificate', 'contract', 'exam_application']),
            'status' => DocumentStatus::Submitted,
            'title' => $this->faker->randomElement(['Identity document', 'Medical certificate', 'Training contract']),
            'number' => $this->faker->optional()->bothify('DOC-####'),
            'issued_at' => now()->subMonths($this->faker->numberBetween(1, 12)),
            'expires_at' => now()->addMonths($this->faker->numberBetween(2, 24)),
            'file_path' => null,
            'notes' => null,
        ];
    }
}
