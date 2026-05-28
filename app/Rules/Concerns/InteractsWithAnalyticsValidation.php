<?php

namespace App\Rules\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait InteractsWithAnalyticsValidation
{
    /**
     * @return array<int, string>
     */
    private function schemaKeys(mixed $schema): array
    {
        if (! is_array($schema) || $schema === []) {
            return [];
        }

        $keys = [];

        foreach ($schema as $key => $definition) {
            if (is_string($key) && ! in_array($key, ['fields', 'filters', 'columns', 'items'], true)) {
                $keys[] = $key;
            }

            if (is_array($definition)) {
                foreach (['key', 'name', 'field', 'code', 'column'] as $field) {
                    if (isset($definition[$field]) && is_string($definition[$field])) {
                        $keys[] = $definition[$field];
                    }
                }

                $keys = array_merge($keys, $this->schemaKeys($definition));
            }
        }

        return array_values(array_unique(array_filter(
            $keys,
            fn (mixed $key): bool => is_string($key) && preg_match('/^[a-z][a-z0-9_.-]*$/', $key) === 1,
        )));
    }

    /**
     * @return array<int, string>
     */
    private function associativeKeys(mixed $values): array
    {
        if (! is_array($values) || $values === []) {
            return [];
        }

        return array_values(array_filter(
            array_keys($values),
            fn (mixed $key): bool => is_string($key) && preg_match('/^[a-z][a-z0-9_.-]*$/', $key) === 1,
        ));
    }

    private function modelTableExists(string $modelClass): bool
    {
        /** @var Model $model */
        $model = new $modelClass;

        return Schema::hasTable($model->getTable());
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }

    private function hasForbiddenAnalyticsScope(array $payload): bool
    {
        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array($key, [
                'tenant_id',
                'company_id',
                'subscription_id',
                'reseller_id',
                'platform_owner_id',
            ], true)) {
                return true;
            }

            if (is_array($value) && $this->hasForbiddenAnalyticsScope($value)) {
                return true;
            }
        }

        return false;
    }
}
