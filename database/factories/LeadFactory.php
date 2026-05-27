<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
use App\Models\TrainingGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'uuid' => (string) Str::uuid(),
            'lead_number' => null,
            'full_name' => $firstName.' '.$lastName,
            'marketing_campaign_id' => null,
            'responsible_manager_id' => null,
            'assigned_by_user_id' => null,
            'assigned_at' => null,
            'branch_id' => Branch::factory(),
            'training_program_id' => Course::factory(),
            'course_category_id' => CourseCategory::factory(),
            'training_group_id' => null,
            'instructor_id' => null,
            'converted_student_profile_id' => null,
            'converted_enrollment_id' => null,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
            'duplicate_of_id' => null,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'messenger' => $this->faker->randomElement(['WhatsApp', 'Telegram', 'Viber']),
            'city' => $this->faker->city(),
            'source' => 'website',
            'status' => LeadStatus::New,
            'license_category' => 'B',
            'preferred_format' => 'hybrid',
            'preferred_language' => 'ru',
            'preferred_time' => 'Evenings',
            'desired_start_date' => null,
            'preferred_gearbox' => null,
            'budget_cents' => $this->faker->numberBetween(50_000, 180_000),
            'is_hot' => false,
            'priority' => 'normal',
            'lead_score' => 0,
            'next_follow_up_at' => null,
            'last_status_changed_at' => null,
            'privacy_accepted_at' => now(),
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'consent_text_version' => 'factory-v1',
            'contacted_at' => null,
            'last_contacted_at' => null,
            'converted_at' => null,
            'closed_at' => null,
            'message' => $this->faker->sentence(12),
            'internal_comment' => null,
            'rejection_reason' => null,
            'lost_reason_code' => null,
            'crm_snapshot' => null,
            'utm_source' => 'website',
            'utm_medium' => 'organic',
            'utm_campaign' => 'foundation',
            'utm_term' => null,
            'utm_content' => null,
            'referrer_url' => null,
            'landing_page' => null,
            'form_page' => null,
            'form_name' => 'enrollment',
            'locale' => 'ru',
            'ip_address' => null,
            'user_agent' => null,
        ];
    }

    public function fromWebsite(): static
    {
        return $this->state(fn (): array => [
            'source' => 'website',
            'form_name' => 'enrollment',
            'status' => LeadStatus::New,
        ]);
    }

    public function callback(): static
    {
        return $this->state(fn (): array => [
            'source' => 'callback',
            'form_name' => 'callback',
            'preferred_time' => 'Tomorrow morning',
            'priority' => 'high',
        ]);
    }

    public function contactForm(): static
    {
        return $this->state(fn (): array => [
            'source' => 'website',
            'form_name' => 'contact',
            'message' => 'Please contact me about driving lessons.',
        ]);
    }

    public function withUtm(): static
    {
        return $this->state(fn (): array => [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'public-website-demo',
            'utm_term' => 'driving school',
            'utm_content' => 'lead-form',
            'referrer_url' => 'https://example.com/referrer',
            'landing_page' => 'https://drivepro.test/?utm_source=google',
            'form_page' => 'https://drivepro.test/apply',
            'locale' => 'en',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Factory Browser',
        ]);
    }

    public function withConsent(): static
    {
        return $this->state(fn (): array => [
            'privacy_accepted_at' => now(),
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'consent_text_version' => 'factory-v1',
        ]);
    }

    public function withoutConsent(): static
    {
        return $this->state(fn (): array => [
            'privacy_accepted_at' => null,
            'consent_accepted' => false,
            'consent_accepted_at' => null,
            'consent_text_version' => null,
        ]);
    }

    public function forCourse(Course|int|null $course = null): static
    {
        return $this->state(function () use ($course): array {
            $courseModel = $course instanceof Course ? $course : null;

            if ($courseModel === null && $course === null) {
                $courseModel = Course::factory()->create();
            }

            return [
                'training_program_id' => $courseModel?->getKey() ?? $course,
                'course_category_id' => $courseModel?->course_category_id,
            ];
        });
    }

    public function forBranch(Branch|int|null $branch = null): static
    {
        return $this->state(function () use ($branch): array {
            $branchModel = $branch instanceof Branch ? $branch : null;

            if ($branchModel === null && $branch === null) {
                $branchModel = Branch::factory()->create();
            }

            return [
                'branch_id' => $branchModel?->getKey() ?? $branch,
            ];
        });
    }

    public function forTrainingGroup(TrainingGroup|int|null $group = null): static
    {
        return $this->state(function () use ($group): array {
            $groupModel = $group instanceof TrainingGroup ? $group : null;

            if ($groupModel === null && $group === null) {
                $groupModel = TrainingGroup::factory()->create();
            }

            return [
                'training_group_id' => $groupModel?->getKey() ?? $group,
                'training_program_id' => $groupModel?->training_program_id,
                'course_category_id' => $groupModel?->course_category_id ?: $groupModel?->course?->course_category_id,
                'branch_id' => $groupModel?->branch_id,
            ];
        });
    }
}
