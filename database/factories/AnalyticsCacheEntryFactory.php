<?php

namespace Database\Factories;

use App\Models\AnalyticsCacheEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsCacheEntry>
 */
class AnalyticsCacheEntryFactory extends Factory
{
    protected $model = AnalyticsCacheEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cache_key' => 'analytics.'.$this->faker->unique()->slug(3),
            'data' => ['records' => 0],
            'tags' => ['analytics'],
            'expires_at' => now()->addMinutes(15),
            'calculated_at' => now(),
        ];
    }

    public function key(string $key): static
    {
        return $this->state(fn (): array => ['cache_key' => $key]);
    }

    public function tagged(array $tags): static
    {
        return $this->state(fn (): array => ['tags' => array_values($tags)]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => ['expires_at' => now()->subMinute()]);
    }

    public function fresh(): static
    {
        return $this->state(fn (): array => ['expires_at' => now()->addMinutes(15)]);
    }
}
