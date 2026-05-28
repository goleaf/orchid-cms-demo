<?php

namespace Database\Factories;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermissionGroup>
 */
class PermissionGroupFactory extends Factory
{
    protected $model = PermissionGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name_translations' => $this->translations($name, $name, $name, $name),
            'description_translations' => null,
            'icon' => 'bs.shield-check',
            'color' => '#2563eb',
            'sort_order' => 0,
            'is_active' => true,
            'is_system' => false,
        ];
    }

    public function website(): static
    {
        return $this->groupState('website', ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona'], 'bs.globe2', '#2563eb');
    }

    public function crm(): static
    {
        return $this->groupState('customer_relationship_management', ['ru' => 'CRM', 'en' => 'CRM', 'lt' => 'CRM', 'pl' => 'CRM'], 'bs.funnel', '#0891b2');
    }

    public function students(): static
    {
        return $this->groupState('students', ['ru' => 'Ученики', 'en' => 'Students', 'lt' => 'Mokiniai', 'pl' => 'Uczniowie'], 'bs.person-lines-fill', '#16a34a');
    }

    public function education(): static
    {
        return $this->groupState('education', ['ru' => 'Обучение', 'en' => 'Education', 'lt' => 'Mokymas', 'pl' => 'Edukacja'], 'bs.mortarboard', '#4f46e5');
    }

    public function schedule(): static
    {
        return $this->groupState('schedule', ['ru' => 'Расписание', 'en' => 'Schedule', 'lt' => 'Tvarkarastis', 'pl' => 'Harmonogram'], 'bs.calendar-week', '#0284c7');
    }

    public function lessons(): static
    {
        return $this->groupState('lessons', ['ru' => 'Занятия', 'en' => 'Lessons', 'lt' => 'Pamokos', 'pl' => 'Lekcje'], 'bs.calendar-check', '#0f766e');
    }

    public function driving(): static
    {
        return $this->groupState('driving', ['ru' => 'Вождение', 'en' => 'Driving', 'lt' => 'Vairavimas', 'pl' => 'Jazda'], 'bs.car-front', '#65a30d');
    }

    public function documents(): static
    {
        return $this->groupState('documents', ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty'], 'bs.folder2-open', '#7c3aed');
    }

    public function finance(): static
    {
        return $this->groupState('finance', ['ru' => 'Финансы', 'en' => 'Finance', 'lt' => 'Finansai', 'pl' => 'Finanse'], 'bs.cash-coin', '#15803d');
    }

    public function exams(): static
    {
        return $this->groupState('exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy'], 'bs.clipboard-check', '#b45309');
    }

    public function notifications(): static
    {
        return $this->groupState('notifications', ['ru' => 'Уведомления', 'en' => 'Notifications', 'lt' => 'Pranesimai', 'pl' => 'Powiadomienia'], 'bs.bell', '#c2410c');
    }

    public function analytics(): static
    {
        return $this->groupState('analytics', ['ru' => 'Аналитика', 'en' => 'Analytics', 'lt' => 'Analitika', 'pl' => 'Analityka'], 'bs.graph-up-arrow', '#4338ca');
    }

    public function security(): static
    {
        return $this->groupState('security', ['ru' => 'Безопасность', 'en' => 'Security', 'lt' => 'Sauga', 'pl' => 'Bezpieczenstwo'], 'bs.shield-lock', '#dc2626');
    }

    public function system(): static
    {
        return $this->groupState('system', ['ru' => 'Система', 'en' => 'System', 'lt' => 'Sistema', 'pl' => 'System'], 'bs.gear', '#475569');
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function systemGroup(): static
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }

    public function translated(): static
    {
        return $this->groupState(
            'translated_permission_group',
            ['ru' => 'Переведенная группа', 'en' => 'Translated group', 'lt' => 'Isversta grupe', 'pl' => 'Przetlumaczona grupa'],
            'bs.translate',
            '#2563eb',
        );
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function groupState(string $code, array $translations, string $icon, string $color): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name_translations' => $translations,
            'description_translations' => $translations,
            'icon' => $icon,
            'color' => $color,
            'is_active' => true,
            'is_system' => true,
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
