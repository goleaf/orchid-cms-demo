<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Enums\TransmissionType;
use App\Models\Course;
use App\Models\TrainingProgram;
use App\Services\TranslatableContentManager;

class CreateOrUpdateCourseAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Course|TrainingProgram|null $course, array $attributes): TrainingProgram
    {
        $course ??= new Course;
        $attributes = $this->assignSortablePosition($course, $attributes);
        $attributes = $this->normalizeAttributes($attributes);
        $attributes = app(GenerateSeoMetadataAction::class)->handle(
            $attributes,
            ['name', 'title'],
            ['short_description', 'description', 'program_summary'],
        );

        $course->fill($attributes);
        $course->save();

        return $course->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        if (array_key_exists('course_id', $attributes) && ! array_key_exists('training_program_id', $attributes)) {
            $attributes['training_program_id'] = $attributes['course_id'];
        }

        if (array_key_exists('price', $attributes) && ! array_key_exists('price_cents', $attributes)) {
            $attributes['price_cents'] = $this->moneyToCents($attributes['price']);
        }

        if (array_key_exists('old_price', $attributes) && ! array_key_exists('old_price_cents', $attributes)) {
            $attributes['old_price_cents'] = filled($attributes['old_price'])
                ? $this->moneyToCents($attributes['old_price'])
                : null;
        }

        if (array_key_exists('image', $attributes) && ! array_key_exists('image_path', $attributes)) {
            $attributes['image_path'] = $attributes['image'];
        }

        if (! array_key_exists('title_translations', $attributes) && array_key_exists('name_translations', $attributes)) {
            $attributes['title_translations'] = $attributes['name_translations'];
        }

        $attributes['title'] ??= $this->fallbackScalar($attributes, 'name')
            ?? $this->fallbackScalar($attributes, 'title')
            ?? tkey('website.courses.fields.name');
        $attributes['description'] ??= $this->fallbackScalar($attributes, 'description');
        $attributes['short_description'] ??= $this->fallbackScalar($attributes, 'short_description');
        $attributes['included_items'] ??= $this->fallbackScalar($attributes, 'includes')
            ?? $this->fallbackScalar($attributes, 'included_items');
        $attributes['extra_costs'] ??= $this->fallbackScalar($attributes, 'excludes')
            ?? $this->fallbackScalar($attributes, 'extra_costs');
        $attributes['currency'] ??= 'EUR';
        $attributes['license_category'] = filled($attributes['license_category'] ?? null) ? $attributes['license_category'] : 'B';
        $attributes['transmission'] = filled($attributes['transmission'] ?? null) ? $attributes['transmission'] : TransmissionType::Manual->value;
        $attributes['theory_hours'] = filled($attributes['theory_hours'] ?? null) ? $attributes['theory_hours'] : 0;
        $attributes['practice_hours'] = filled($attributes['practice_hours'] ?? null) ? $attributes['practice_hours'] : 0;
        $attributes['duration_weeks'] = filled($attributes['duration_weeks'] ?? null) ? $attributes['duration_weeks'] : 8;
        $attributes['price_cents'] = filled($attributes['price_cents'] ?? null) ? $attributes['price_cents'] : 0;

        return $attributes;
    }

    private function moneyToCents(mixed $value): ?int
    {
        return filled($value) ? (int) round(((float) $value) * 100) : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function fallbackScalar(array $attributes, string $field): ?string
    {
        $manager = app(TranslatableContentManager::class);
        $translations = $attributes[$manager->translationAttribute($field)] ?? [];
        $value = is_array($translations) ? $manager->defaultValue($translations) : null;

        return filled($value) ? (string) $value : null;
    }
}
