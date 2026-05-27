<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SeedsFactoryBackedDictionaries
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $records
     */
    protected function seedFactoryBackedDictionary(string $modelClass, string $keyColumn, array $records): void
    {
        foreach ($records as $sortOrder => $record) {
            $factory = $modelClass::factory();
            $state = $record['state'] ?? null;

            if (is_string($state) && method_exists($factory, $state)) {
                $factory = $factory->{$state}();
            }

            /** @var Model $dictionary */
            $dictionary = $factory->make([
                $keyColumn => $record[$keyColumn],
                'sort_order' => $record['sort_order'] ?? (($sortOrder + 1) * 10),
                ...($record['attributes'] ?? []),
            ]);

            $attributes = $dictionary->only($dictionary->getFillable());
            unset($attributes[$keyColumn]);

            $modelClass::query()->updateOrCreate(
                [$keyColumn => $record[$keyColumn]],
                $attributes,
            );
        }
    }
}
