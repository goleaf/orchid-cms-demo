<?php

namespace Database\Factories;

use App\Models\AnalyticsCache;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsCache>
 */
class AnalyticsCacheFactory extends Factory
{
    protected $model = AnalyticsCache::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'analytics.'.$this->faker->unique()->slug(3),
            'group' => 'analytics',
            'value' => ['records' => 0],
            'tags' => ['analytics'],
            'expires_at' => now()->addMinutes(15),
            'refreshed_at' => now(),
            'created_by_id' => null,
        ];
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
        ]);
    }
}
