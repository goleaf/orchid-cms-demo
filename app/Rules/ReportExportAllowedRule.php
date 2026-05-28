<?php

namespace App\Rules;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\User;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReportExportAllowedRule implements ValidationRule
{
    use InteractsWithAnalyticsValidation;

    /**
     * @param  array<int, string>  $allowedFormats
     */
    public function __construct(
        private readonly ?ReportRun $run = null,
        private readonly ?ReportDefinition $definition = null,
        private readonly ?User $user = null,
        private readonly array $allowedFormats = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->formatIsAllowed($value)
            || ! $this->runIsExportable()
            || ! $this->definitionIsExportable()
            || ! $this->userCanExport()) {
            $fail(tkey('analytics.validation.export_not_allowed'));
        }
    }

    private function formatIsAllowed(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value) && in_array($value, $this->allowedFormats ?: ReportExportFormat::values(), true);
    }

    private function runIsExportable(): bool
    {
        if (! $this->run instanceof ReportRun) {
            return true;
        }

        return $this->enumValue($this->run->getAttribute('status')) === AnalyticsRunStatus::Completed->value;
    }

    private function definitionIsExportable(): bool
    {
        $definition = $this->definition;

        if (! $definition instanceof ReportDefinition && $this->run instanceof ReportRun) {
            $definition = $this->run->relationLoaded('definition') ? $this->run->definition : null;
        }

        if (! $definition instanceof ReportDefinition) {
            return true;
        }

        return (bool) $definition->getAttribute('is_active');
    }

    private function userCanExport(): bool
    {
        if (! $this->user instanceof User) {
            return true;
        }

        if (! $this->user->hasAccess('analytics.reports.export')) {
            return false;
        }

        $definition = $this->definition;

        if (! $definition instanceof ReportDefinition && $this->run instanceof ReportRun && $this->run->relationLoaded('definition')) {
            $definition = $this->run->definition;
        }

        $permissions = $definition instanceof ReportDefinition ? $definition->getAttribute('permissions') : [];

        if (! is_array($permissions)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (is_string($permission) && $permission !== '' && ! $this->user->hasAccess($permission)) {
                return false;
            }
        }

        return true;
    }
}
