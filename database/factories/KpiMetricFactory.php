<?php

namespace Database\Factories;

use App\Enums\KpiMetricGroup;
use App\Enums\KpiUnit;
use App\Models\KpiMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KpiMetric>
 */
class KpiMetricFactory extends Factory
{
    protected $model = KpiMetric::class;

    public function configure(): static
    {
        return $this->afterMaking(function (KpiMetric $metric): void {
            if (filled($metric->category) && $metric->metric_group === KpiMetricGroup::Students->value) {
                $metric->metric_group = $this->groupForLegacyCategory((string) $metric->category);
            }

            if (filled($metric->value_type)) {
                $legacyUnit = $this->unitForLegacyValueType((string) $metric->value_type);

                if (! in_array((string) $metric->unit, KpiUnit::values(), true) || ((string) $metric->unit === KpiUnit::Count->value && $legacyUnit !== KpiUnit::Count->value)) {
                    $metric->unit = $legacyUnit;
                }
            }

            if (blank($metric->calculation_type) && filled($metric->calculation)) {
                $metric->calculation_type = (string) $metric->calculation;
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(3);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(10)),
            'metric_group' => KpiMetricGroup::Students->value,
            'category' => KpiMetricGroup::Students->value,
            'value_type' => KpiUnit::Count->value,
            'unit' => KpiUnit::Count->value,
            'calculation_type' => 'manual',
            'calculation' => null,
            'source' => null,
            'is_system' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'settings' => [],
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'is_system' => true,
            'created_by_id' => null,
            'updated_by_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function group(KpiMetricGroup|string $group): static
    {
        $value = $group instanceof KpiMetricGroup ? $group->value : $group;

        return $this->state(fn (): array => [
            'metric_group' => $value,
            'category' => $value,
        ]);
    }

    public function unit(KpiUnit|string $unit): static
    {
        $value = $unit instanceof KpiUnit ? $unit->value : $unit;

        return $this->state(fn (): array => [
            'unit' => $value,
            'value_type' => $value,
        ]);
    }

    public function calculationType(string $type): static
    {
        return $this->state(fn (): array => [
            'calculation_type' => $type,
            'calculation' => $type,
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

    private function groupForLegacyCategory(string $category): string
    {
        return match ($category) {
            'crm', 'sales' => KpiMetricGroup::Sales->value,
            'finance' => KpiMetricGroup::Finance->value,
            'education' => KpiMetricGroup::Education->value,
            'exams' => KpiMetricGroup::Exams->value,
            'notifications' => KpiMetricGroup::Notifications->value,
            'operations' => KpiMetricGroup::Lessons->value,
            default => KpiMetricGroup::Staff->value,
        };
    }

    private function unitForLegacyValueType(string $valueType): string
    {
        return match ($valueType) {
            'percent', 'percentage' => KpiUnit::Percent->value,
            'money', 'currency' => KpiUnit::Money->value,
            'hours' => KpiUnit::Hours->value,
            'days' => KpiUnit::Days->value,
            'ratio' => KpiUnit::Ratio->value,
            default => KpiUnit::Count->value,
        };
    }
}
