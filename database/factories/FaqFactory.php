<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'faqable_type' => null,
            'faqable_id' => null,
            'question_translations' => [
                'ru' => $this->faker->sentence(8),
                'en' => $this->faker->sentence(8),
            ],
            'answer_translations' => [
                'ru' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'is_active' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
