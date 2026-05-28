<?php

namespace Database\Factories;

use App\Enums\AnalyticsDashboardAudience;
use App\Models\AnalyticsDashboard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AnalyticsDashboard>
 */
class AnalyticsDashboardFactory extends Factory
{
    protected $model = AnalyticsDashboard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $audience = $this->faker->randomElement(AnalyticsDashboardAudience::values());
        $code = $audience.'_'.Str::slug($this->faker->unique()->words(2, true), '_');
        $name = str($code)->replace(['_', '-'], ' ')->title()->toString();

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(8)),
            'audience' => $audience,
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function audience(AnalyticsDashboardAudience|string $audience): static
    {
        $value = $audience instanceof AnalyticsDashboardAudience ? $audience->value : $audience;

        return $this->state(fn (): array => [
            'audience' => $value,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (): array => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
