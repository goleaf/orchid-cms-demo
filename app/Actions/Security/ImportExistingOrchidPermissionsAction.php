<?php

namespace App\Actions\Security;

use App\Models\PermissionRegistryItem;
use App\Support\Access\SuperadminPermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Orchid\Support\Facades\Dashboard;
use Throwable;

class ImportExistingOrchidPermissionsAction
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return collect()
            ->merge($this->superadminPermissionCodes())
            ->merge($this->dashboardPermissionCodes())
            ->merge($this->providerSourcePermissionCodes())
            ->filter(fn (mixed $code): bool => is_string($code) && $this->validPermissionCode($code))
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $code, int $index): array => $this->normalize($code, $index))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        return collect($this->handle())
            ->pluck('code')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function superadminPermissionCodes(): array
    {
        try {
            return SuperadminPermissions::all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, string>
     */
    private function dashboardPermissionCodes(): array
    {
        try {
            return Dashboard::getPermission()
                ->flatMap(fn (mixed $items): Collection => collect($items)
                    ->map(fn (mixed $item): ?string => is_array($item) ? ($item['slug'] ?? null) : null))
                ->filter()
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, string>
     */
    private function providerSourcePermissionCodes(): array
    {
        $path = app_path('Orchid/PlatformProvider.php');

        if (! File::exists($path)) {
            return [];
        }

        try {
            preg_match_all("/addPermission\\('([^']+)'/", File::get($path), $matches);

            return $matches[1] ?? [];
        } catch (Throwable) {
            return [];
        }
    }

    private function validPermissionCode(string $code): bool
    {
        return preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $code) === 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(string $code, int $index): array
    {
        $module = $this->moduleFor($code);
        $groupCode = $this->groupCodeFor($code, $module);
        $riskLevel = $this->riskLevelFor($code);
        $label = $this->readableLabel($code);

        return [
            'code' => $code,
            'module' => $module,
            'group_code' => $groupCode,
            'risk_level' => $riskLevel,
            'name_translations' => $this->translations($label),
            'description_translations' => $this->translations($this->descriptionFor($label, $riskLevel)),
            'is_active' => true,
            'is_system' => true,
            'sort_order' => ($index + 1) * 10,
        ];
    }

    private function moduleFor(string $code): string
    {
        return match (true) {
            str_starts_with($code, 'website.') => 'website',
            str_starts_with($code, 'crm.'), str_starts_with($code, 'platform.crm.'), str_starts_with($code, 'platform.marketing.') => 'customer_relationship_management',
            str_starts_with($code, 'students.'), str_starts_with($code, 'platform.crm.students') => 'students',
            str_starts_with($code, 'education.'), str_starts_with($code, 'platform.lms.'), str_starts_with($code, 'platform.operations.groups') => 'education',
            str_starts_with($code, 'schedule.'), str_starts_with($code, 'platform.schedule.') => 'schedule',
            str_starts_with($code, 'lessons.') => 'lessons',
            str_starts_with($code, 'driving.'), str_starts_with($code, 'platform.fleet.'), str_starts_with($code, 'platform.operations.instructors'), str_starts_with($code, 'platform.operations.branches') => 'driving',
            str_starts_with($code, 'documents.'), str_starts_with($code, 'platform.documents') => 'documents',
            str_starts_with($code, 'finance.'), str_starts_with($code, 'platform.finance.') => 'finance',
            str_starts_with($code, 'exams.'), str_starts_with($code, 'platform.exams') => 'exams',
            str_starts_with($code, 'notifications.'), str_starts_with($code, 'communications.') => 'notifications',
            str_starts_with($code, 'analytics.') => 'analytics',
            str_starts_with($code, 'security.'), str_starts_with($code, 'platform.systems.users'), str_starts_with($code, 'platform.systems.roles') => 'security',
            default => 'system',
        };
    }

    private function groupCodeFor(string $code, string $module): string
    {
        if (str_starts_with($code, 'platform.operations.branches')) {
            return 'driving';
        }

        return in_array($module, PermissionRegistryItem::MODULES, true)
            ? $module
            : 'system';
    }

    private function riskLevelFor(string $code): string
    {
        if (
            str_contains($code, 'delete')
            || str_contains($code, 'archive')
            || str_contains($code, 'block')
            || str_contains($code, 'cancel')
            || str_contains($code, 'override')
            || str_starts_with($code, 'security.')
            || str_starts_with($code, 'platform.systems.')
        ) {
            return PermissionRegistryItem::RISK_CRITICAL;
        }

        if (
            str_contains($code, 'manage')
            || str_contains($code, 'create')
            || str_contains($code, 'update')
            || str_contains($code, 'import')
            || str_contains($code, 'export')
            || str_contains($code, 'send')
            || str_contains($code, 'record')
            || str_contains($code, 'approve')
        ) {
            return PermissionRegistryItem::RISK_HIGH;
        }

        if (str_contains($code, 'view') || str_ends_with($code, '.index') || str_ends_with($code, '.main')) {
            return PermissionRegistryItem::RISK_LOW;
        }

        return PermissionRegistryItem::RISK_NORMAL;
    }

    private function readableLabel(string $code): string
    {
        return str($code)
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->toString();
    }

    private function descriptionFor(string $label, string $riskLevel): string
    {
        return $label.' permission registered from the local Orchid permission source. Risk level: '.$riskLevel.'.';
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
