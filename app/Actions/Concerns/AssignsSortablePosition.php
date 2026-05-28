<?php

namespace App\Actions\Concerns;

use Illuminate\Database\Eloquent\Model;

trait AssignsSortablePosition
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function assignSortablePosition(Model $model, array $attributes): array
    {
        if (! $model->exists && ! array_key_exists('sort_order', $attributes)) {
            $attributes['sort_order'] = ((int) $model->newQuery()->max('sort_order')) + 10;
        }

        return $attributes;
    }
}
