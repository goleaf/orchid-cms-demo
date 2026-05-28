<?php

namespace App\Rules;

use App\Models\ReportDefinition;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReportFilterValueAllowedRule implements ValidationRule
{
    /**
     * @param  array<int, string|int|float|bool>  $allowedValues
     */
    public function __construct(
        private readonly ?ReportDefinition $definition = null,
        private readonly ?string $filterKey = null,
        private readonly array $allowedValues = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->valueShapeIsSafe($value)) {
            $fail(tkey('analytics.validation.filter_value_not_allowed'));

            return;
        }

        $allowed = $this->allowedValues();

        if ($allowed === []) {
            return;
        }

        foreach ($this->values($value) as $candidate) {
            if (! in_array($candidate, $allowed, true)) {
                $fail(tkey('analytics.validation.filter_value_not_allowed'));

                return;
            }
        }
    }

    private function valueShapeIsSafe(mixed $value): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }

        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_scalar($item) && $item !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, string|int|float|bool|null>
     */
    private function values(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [$value];
    }

    /**
     * @return array<int, string|int|float|bool>
     */
    private function allowedValues(): array
    {
        if ($this->allowedValues !== []) {
            return $this->allowedValues;
        }

        if (! $this->definition instanceof ReportDefinition || $this->filterKey === null) {
            return [];
        }

        return $this->allowedValuesFromSchema($this->definition->getAttribute('filters_schema'), $this->filterKey);
    }

    /**
     * @return array<int, string|int|float|bool>
     */
    private function allowedValuesFromSchema(mixed $schema, string $filterKey): array
    {
        if (! is_array($schema)) {
            return [];
        }

        foreach ($schema as $key => $definition) {
            if ($key === $filterKey && is_array($definition)) {
                return $this->extractAllowedValues($definition);
            }

            if (is_array($definition)) {
                $definitionKey = $definition['key'] ?? $definition['name'] ?? $definition['field'] ?? $definition['code'] ?? null;

                if ($definitionKey === $filterKey) {
                    return $this->extractAllowedValues($definition);
                }

                $nested = $this->allowedValuesFromSchema($definition, $filterKey);

                if ($nested !== []) {
                    return $nested;
                }
            }
        }

        return [];
    }

    /**
     * @return array<int, string|int|float|bool>
     */
    private function extractAllowedValues(array $definition): array
    {
        foreach (['allowed_values', 'values', 'options', 'enum'] as $key) {
            if (! isset($definition[$key]) || ! is_array($definition[$key])) {
                continue;
            }

            return array_values(array_filter(array_map(
                fn (mixed $option): mixed => is_array($option)
                    ? ($option['value'] ?? $option['code'] ?? $option['key'] ?? null)
                    : $option,
                $definition[$key],
            ), fn (mixed $value): bool => is_scalar($value)));
        }

        return [];
    }
}
