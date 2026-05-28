<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Models\Branch;
use App\Services\TranslatableContentManager;

class CreateOrUpdateBranchAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?Branch $branch, array $attributes): Branch
    {
        $branch ??= new Branch;
        $attributes = $this->assignSortablePosition($branch, $attributes);
        $attributes = app(GenerateSeoMetadataAction::class)->handle(
            $attributes,
            ['name'],
            ['description', 'address', 'country', 'city'],
        );
        $attributes['name'] ??= $this->fallbackScalar($attributes, 'name', tkey('website.branches.fields.name'));
        $attributes['country'] ??= $this->fallbackScalar($attributes, 'country', 'Lithuania');
        $attributes['city'] ??= $this->fallbackScalar($attributes, 'city', '');
        $attributes['address'] ??= $this->fallbackScalar($attributes, 'address', '');
        $attributes['description'] ??= $this->fallbackScalar($attributes, 'description');
        $attributes['working_hours'] ??= $this->fallbackScalar($attributes, 'working_hours');

        $branch->fill($attributes);
        $branch->save();

        return $branch->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function fallbackScalar(array $attributes, string $field, ?string $fallback = null): ?string
    {
        $manager = app(TranslatableContentManager::class);
        $translations = $attributes[$manager->translationAttribute($field)] ?? [];
        $value = is_array($translations) ? $manager->defaultValue($translations) : null;

        return filled($value) ? (string) $value : $fallback;
    }
}
