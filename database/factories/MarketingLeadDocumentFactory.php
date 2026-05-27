<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\MarketingLeadDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadDocument>
 */
class MarketingLeadDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_lead_id' => MarketingLead::factory(),
            'original_name' => 'document.pdf',
            'path' => 'lead-documents/document.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(50_000, 200_000),
        ];
    }
}
