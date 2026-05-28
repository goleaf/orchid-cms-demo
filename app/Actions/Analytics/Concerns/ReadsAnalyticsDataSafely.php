<?php

namespace App\Actions\Analytics\Concerns;

use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

trait ReadsAnalyticsDataSafely
{
    /**
     * @param  array<int, string>|string  $permissions
     *
     * @throws AuthorizationException
     */
    private function authorizeAnalyticsAccess(?User $user, array|string $permissions): void
    {
        if ($user === null) {
            return;
        }

        foreach ((array) $permissions as $permission) {
            if ($permission !== '' && ! $user->hasAccess($permission)) {
                throw new AuthorizationException(tkey('analytics.validation.permission_denied'));
            }
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function analyticsTableExists(string $modelClass): bool
    {
        $table = $this->analyticsTableFor($modelClass);

        return $table !== null && Schema::hasTable($table);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function analyticsTableFor(string $modelClass): ?string
    {
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        return (new $modelClass)->getTable();
    }

    /**
     * @param  Builder<Model>|class-string<Model>  $source
     */
    private function analyticsColumnExists(Builder|string $source, string $column): bool
    {
        $table = $source instanceof Builder
            ? $source->getModel()->getTable()
            : $this->analyticsTableFor($source);

        return $table !== null && Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  Closure(Builder<Model>): mixed|null  $callback
     */
    private function analyticsCount(string $modelClass, ?Closure $callback = null): int
    {
        $query = $this->analyticsQuery($modelClass);

        if ($query === null) {
            return 0;
        }

        if ($callback !== null) {
            $callback($query);
        }

        return (int) $query->count();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  Closure(Builder<Model>): mixed|null  $callback
     */
    private function analyticsSum(string $modelClass, string $column, ?Closure $callback = null): float
    {
        $query = $this->analyticsQuery($modelClass);

        if ($query === null || ! $this->analyticsColumnExists($query, $column)) {
            return 0.0;
        }

        if ($callback !== null) {
            $callback($query);
        }

        return (float) $query->sum($column);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Builder<Model>|null
     */
    private function analyticsQuery(string $modelClass): ?Builder
    {
        if (! $this->analyticsTableExists($modelClass)) {
            return null;
        }

        return $modelClass::query();
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAnalyticsFilters(Builder $query, array $filters, string $dateColumn = 'created_at'): Builder
    {
        if ($this->analyticsColumnExists($query, $dateColumn)) {
            if (filled($filters['period_start'] ?? null)) {
                $query->where($dateColumn, '>=', Carbon::parse((string) $filters['period_start'])->startOfDay());
            }

            if (filled($filters['period_end'] ?? null)) {
                $query->where($dateColumn, '<=', Carbon::parse((string) $filters['period_end'])->endOfDay());
            }
        }

        return $this->applyAnalyticsDimensions($query, $filters);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAnalyticsDimensions(Builder $query, array $filters): Builder
    {
        foreach ([
            'branch_id',
            'user_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'manager_id',
            'responsible_manager_id',
        ] as $column) {
            if (filled($filters[$column] ?? null) && $this->analyticsColumnExists($query, $column)) {
                $query->where($column, (int) $filters[$column]);
            }
        }

        return $query;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $values
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    private function analyticsCountByKnownValues(string $modelClass, string $column, array $values, array $filters = []): array
    {
        if (! $this->analyticsTableExists($modelClass) || ! $this->analyticsColumnExists($modelClass, $column)) {
            return array_fill_keys($values, 0);
        }

        $counts = [];

        foreach ($values as $value) {
            $counts[$value] = $this->analyticsCount(
                $modelClass,
                fn (Builder $query): Builder => $this->applyAnalyticsFilters($query, $filters)->where($column, $value),
            );
        }

        return $counts;
    }

    /**
     * @param  array<string, class-string<Model>>  $modules
     * @return array<string, bool>
     */
    private function analyticsModuleAvailability(array $modules): array
    {
        $availability = [];

        foreach ($modules as $name => $modelClass) {
            $availability[$name] = $this->analyticsTableExists($modelClass);
        }

        return $availability;
    }

    /**
     * @param  Model|class-string<Model>  $model
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function analyticsExistingAttributes(Model|string $model, array $attributes): array
    {
        $table = $model instanceof Model ? $model->getTable() : $this->analyticsTableFor($model);

        if ($table === null || ! Schema::hasTable($table)) {
            return [];
        }

        return collect($attributes)
            ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn($table, $column))
            ->all();
    }

    private function analyticsEnumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return filled($value) ? (string) $value : null;
    }
}
