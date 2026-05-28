<?php

namespace Database\Factories;

use App\Enums\AnalyticsReportType;
use App\Enums\ReportGroup;
use App\Models\ReportDefinition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReportDefinition>
 */
class ReportDefinitionFactory extends Factory
{
    protected $model = ReportDefinition::class;

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
            'report_group' => ReportGroup::System->value,
            'data_source' => null,
            'filters_schema' => [],
            'columns_schema' => [],
            'permissions' => [],
            'report_type' => $this->faker->randomElement(AnalyticsReportType::cases()),
            'source_model' => null,
            'default_filters' => [],
            'column_config' => [],
            'schedule' => null,
            'is_system' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function type(AnalyticsReportType $type): static
    {
        return $this->state(fn (): array => ['report_type' => $type]);
    }

    public function group(ReportGroup|string $group): static
    {
        $value = $group instanceof ReportGroup ? $group->value : $group;

        return $this->state(fn (): array => [
            'report_group' => $value,
        ]);
    }

    public function dataSource(string $source): static
    {
        return $this->state(fn (): array => [
            'data_source' => $source,
            'source_model' => $source,
        ]);
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
