<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MarketingLead>
 */
class MarketingLeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'marketing_campaign_id' => MarketingCampaign::factory(),
            'responsible_manager_id' => null,
            'branch_id' => Branch::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'training_group_id' => null,
            'instructor_id' => null,
            'converted_student_profile_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->optional()->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'messenger' => $this->faker->randomElement(['WhatsApp', 'Telegram', 'Viber']),
            'city' => $this->faker->city(),
            'source' => $this->faker->randomElement(['website', 'facebook', 'google_ads', 'referral']),
            'status' => LeadStatus::New,
            'license_category' => 'B',
            'preferred_format' => 'mixed',
            'preferred_language' => 'English',
            'preferred_time' => 'Evenings',
            'budget_cents' => $this->faker->optional()->numberBetween(50_000, 180_000),
            'privacy_accepted_at' => now(),
            'contacted_at' => null,
            'converted_at' => null,
            'message' => $this->faker->optional()->sentence(),
            'rejection_reason' => null,
            'lost_reason_code' => null,
            'crm_snapshot' => null,
            'utm_source' => 'website',
            'utm_medium' => 'organic',
            'utm_campaign' => 'factory',
            'utm_term' => null,
            'utm_content' => null,
            'referrer_url' => null,
            'landing_page' => null,
            'form_page' => null,
            'form_name' => null,
            'locale' => 'ru',
            'ip_address' => null,
            'user_agent' => null,
        ];
    }

    public function websiteLead(): static
    {
        return $this->state(fn (): array => [
            'source' => 'website',
            'form_name' => 'enrollment',
            'locale' => app()->getLocale(),
            'privacy_accepted_at' => now(),
        ]);
    }
}
