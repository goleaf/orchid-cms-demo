<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Models\CourseCategory;

class CreateOrUpdateCourseCategoryAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?CourseCategory $category, array $attributes): CourseCategory
    {
        $category ??= new CourseCategory;
        $attributes = $this->assignSortablePosition($category, $attributes);
        $attributes = app(GenerateSeoMetadataAction::class)->handle(
            $attributes,
            ['name'],
            ['short_description', 'description'],
        );

        $category->fill($attributes);
        $category->save();

        return $category->refresh();
    }
}
