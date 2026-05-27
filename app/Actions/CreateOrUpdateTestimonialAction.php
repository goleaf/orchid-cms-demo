<?php

namespace App\Actions;

use App\Enums\ReviewStatus;
use App\Models\Testimonial;
use App\Services\TranslatableContentManager;

class CreateOrUpdateTestimonialAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?Testimonial $testimonial, array $attributes): Testimonial
    {
        $testimonial ??= new Testimonial;

        if (array_key_exists('course_id', $attributes) && ! array_key_exists('training_program_id', $attributes)) {
            $attributes['training_program_id'] = $attributes['course_id'];
        }

        $attributes['author_name'] ??= $this->fallbackScalar($attributes, 'name');
        $attributes['body'] ??= $this->fallbackScalar($attributes, 'text', '');
        $attributes['title'] ??= $attributes['author_name'] ?? null;
        $attributes['status'] ??= ReviewStatus::Published;

        $testimonial->fill($attributes);
        $testimonial->save();

        return $testimonial->refresh();
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
