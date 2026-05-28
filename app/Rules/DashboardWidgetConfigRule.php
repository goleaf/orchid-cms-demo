<?php

namespace App\Rules;

use App\Enums\DashboardWidgetType;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DashboardWidgetConfigRule implements ValidationRule
{
    use InteractsWithAnalyticsValidation;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_array($value) || $this->hasForbiddenAnalyticsScope($value) || ! $this->configShapeIsValid($value)) {
            $fail(tkey('analytics.validation.invalid_widget_config'));
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function configShapeIsValid(array $config): bool
    {
        foreach (['metric', 'metric_code', 'data_source', 'report_code'] as $key) {
            if (isset($config[$key]) && (! is_string($config[$key]) || preg_match('/^[a-z][a-z0-9_.-]*$/', $config[$key]) !== 1)) {
                return false;
            }
        }

        foreach (['widget_type', 'type'] as $key) {
            if (isset($config[$key]) && (! is_string($config[$key]) || ! in_array($config[$key], DashboardWidgetType::values(), true))) {
                return false;
            }
        }

        if (isset($config['columns']) && ! $this->stringList($config['columns'])) {
            return false;
        }

        if (isset($config['refresh_seconds']) && (! is_int($config['refresh_seconds']) || $config['refresh_seconds'] < 0)) {
            return false;
        }

        return true;
    }

    private function stringList(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_string($item) || preg_match('/^[a-z][a-z0-9_.-]*$/', $item) !== 1) {
                return false;
            }
        }

        return true;
    }
}
