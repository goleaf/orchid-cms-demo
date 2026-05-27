<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
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
}
