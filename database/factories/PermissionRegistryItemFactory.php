<?php

namespace Database\Factories;

use App\Models\PermissionGroup;
use App\Models\PermissionRegistryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermissionRegistryItem>
 */
class PermissionRegistryItemFactory extends Factory
{
    protected $model = PermissionRegistryItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'custom.'.$this->faker->unique()->slug();
        $name = str($code)->replace(['.', '-', '_'], ' ')->title()->toString();

        return [
            'permission_group_id' => PermissionGroup::factory()->systemGroup(),
            'code' => $code,
            'name_translations' => $this->translations($name, $name, $name, $name),
            'description_translations' => null,
            'module' => 'system',
            'risk_level' => PermissionRegistryItem::RISK_NORMAL,
            'is_active' => true,
            'is_system' => true,
            'sort_order' => 0,
        ];
    }

    public function lowRisk(): static
    {
        return $this->state(fn (): array => ['risk_level' => PermissionRegistryItem::RISK_LOW]);
    }

    public function normalRisk(): static
    {
        return $this->state(fn (): array => ['risk_level' => PermissionRegistryItem::RISK_NORMAL]);
    }

    public function highRisk(): static
    {
        return $this->state(fn (): array => ['risk_level' => PermissionRegistryItem::RISK_HIGH]);
    }

    public function criticalRisk(): static
    {
        return $this->state(fn (): array => ['risk_level' => PermissionRegistryItem::RISK_CRITICAL]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function systemPermission(): static
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }

    public function customPermission(): static
    {
        return $this->state(fn (): array => ['is_system' => false]);
    }

    public function website(): static
    {
        return $this->moduleState('website', 'website');
    }

    public function crm(): static
    {
        return $this->moduleState('customer_relationship_management', 'crm');
    }

    public function students(): static
    {
        return $this->moduleState('students', 'students');
    }

    public function education(): static
    {
        return $this->moduleState('education', 'education');
    }

    public function schedule(): static
    {
        return $this->moduleState('schedule', 'schedule');
    }

    public function lessons(): static
    {
        return $this->moduleState('lessons', 'lessons');
    }

    public function driving(): static
    {
        return $this->moduleState('driving', 'driving');
    }

    public function documents(): static
    {
        return $this->moduleState('documents', 'documents');
    }

    public function finance(): static
    {
        return $this->moduleState('finance', 'finance');
    }

    public function exams(): static
    {
        return $this->moduleState('exams', 'exams');
    }

    public function notifications(): static
    {
        return $this->moduleState('notifications', 'notifications');
    }

    public function analytics(): static
    {
        return $this->moduleState('analytics', 'analytics');
    }

    public function security(): static
    {
        return $this->moduleState('security', 'security');
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => $this->translations('Переведенное право', 'Translated permission', 'Isverstas leidimas', 'Przetlumaczone uprawnienie'),
            'description_translations' => $this->translations('Описание права', 'Permission description', 'Leidimo aprasymas', 'Opis uprawnienia'),
        ]);
    }

    private function moduleState(string $module, string $prefix): static
    {
        return $this->state(fn (): array => [
            'code' => $prefix.'.'.$this->faker->unique()->slug(),
            'module' => $module,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}
