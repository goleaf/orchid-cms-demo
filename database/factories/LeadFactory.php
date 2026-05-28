<?php

namespace Database\Factories;

use App\Enums\CourseFormat;
use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
use App\Models\LeadTag;
use App\Models\TrainingGroup;
use App\Models\User;
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
            'preferred_format' => CourseFormat::Hybrid->value,
            'preferred_language' => 'ru',
            'preferred_time' => 'Evenings',
            'desired_start_date' => null,
            'preferred_gearbox' => null,
            'budget_cents' => $this->faker->numberBetween(50_000, 180_000),
            'is_hot' => false,
            'priority' => LeadTaskPriority::Normal->value,
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

    public function newLead(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::New,
            'closed_at' => null,
            'converted_at' => null,
        ]);
    }

    public function noAnswer(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::NoAnswer,
            'last_contacted_at' => now()->subHour(),
            'contacted_at' => now()->subHour(),
        ]);
    }

    public function contacted(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Contacted,
            'last_contacted_at' => now()->subMinutes(30),
            'contacted_at' => now()->subMinutes(30),
        ]);
    }

    public function waitingDocuments(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::WaitingDocuments,
            'next_follow_up_at' => now()->addDay(),
        ]);
    }

    public function waitingPayment(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::WaitingPayment,
            'next_follow_up_at' => now()->addDay(),
        ]);
    }

    public function readyToEnroll(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::ReadyToEnroll,
            'next_follow_up_at' => now()->addHours(2),
        ]);
    }

    public function enrolled(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Enrolled,
            'converted_at' => now(),
            'closed_at' => now(),
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Lost,
            'lost_reason_code' => 'other',
            'closed_at' => now(),
        ]);
    }

    public function duplicate(?Lead $original = null): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Duplicate,
            'duplicate_of_id' => $original?->getKey() ?? Lead::factory(),
            'closed_at' => now(),
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Spam,
            'closed_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Archived,
            'closed_at' => now(),
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::New,
            'closed_at' => null,
            'converted_at' => null,
            'duplicate_of_id' => null,
            'lost_reason_code' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Archived,
            'closed_at' => now(),
        ]);
    }

    public function callback(): static
    {
        return $this->state(fn (): array => [
            'source' => 'callback',
            'form_name' => 'callback',
            'preferred_time' => 'Tomorrow morning',
            'priority' => LeadTaskPriority::High->value,
        ]);
    }

    public function contactForm(): static
    {
        return $this->state(fn (): array => [
            'source' => 'contact_form',
            'form_name' => 'contact',
            'message' => 'Please contact me about driving lessons.',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (): array => [
            'source' => 'phone',
            'form_name' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'landing_page' => null,
            'form_page' => null,
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

    public function assigned(User|int|null $manager = null): static
    {
        return $this->state(function () use ($manager): array {
            $managerModel = $manager instanceof User ? $manager : null;

            if ($managerModel === null && $manager === null) {
                $managerModel = User::factory()->create();
            }

            return [
                'responsible_manager_id' => $managerModel?->getKey() ?? $manager,
                'assigned_at' => now(),
            ];
        });
    }

    public function unassigned(): static
    {
        return $this->state(fn (): array => [
            'responsible_manager_id' => null,
            'assigned_by_user_id' => null,
            'assigned_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->open()->state(fn (): array => [
            'next_follow_up_at' => now()->subHour(),
        ]);
    }

    public function dueToday(): static
    {
        return $this->open()->state(fn (): array => [
            'next_follow_up_at' => now()->addHours(2),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Enrolled,
            'converted_at' => now(),
            'closed_at' => now(),
        ]);
    }

    public function notConverted(): static
    {
        return $this->state(fn (): array => [
            'converted_at' => null,
            'converted_student_profile_id' => null,
            'converted_enrollment_id' => null,
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

    public function withCourse(Course|int|null $course = null): static
    {
        return $this->forCourse($course);
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

    public function withBranch(Branch|int|null $branch = null): static
    {
        return $this->forBranch($branch);
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

    public function withTrainingGroup(TrainingGroup|int|null $group = null): static
    {
        return $this->forTrainingGroup($group);
    }

    public function highPriority(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::High->value]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => LeadTaskPriority::Urgent->value]);
    }

    public function hot(): static
    {
        return $this->state(fn (): array => [
            'is_hot' => true,
            'priority' => LeadTaskPriority::High->value,
            'lead_score' => 85,
        ])->withTags(['hot']);
    }

    /**
     * @param  array<int, LeadTag|int|string>|null  $tags
     */
    public function withTags(?array $tags = null): static
    {
        return $this->afterCreating(function (Lead $lead) use ($tags): void {
            $tagIds = collect($tags ?? [LeadTag::factory()->hot()->create()])
                ->map(function (LeadTag|int|string $tag): int {
                    if ($tag instanceof LeadTag) {
                        return (int) $tag->getKey();
                    }

                    if (is_int($tag)) {
                        return $tag;
                    }

                    return (int) LeadTag::query()
                        ->firstOrCreate(
                            ['slug' => $tag],
                            LeadTag::factory()
                                ->state(['slug' => $tag])
                                ->make()
                                ->only((new LeadTag)->getFillable()),
                        )
                        ->getKey();
                })
                ->all();

            $lead->tags()->syncWithoutDetaching($tagIds);
        });
    }
}
