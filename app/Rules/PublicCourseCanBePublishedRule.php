<?php

namespace App\Rules;

use App\Models\Course;
use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicCourseCanBePublishedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $course = filled($value) ? Course::query()->find($value) : null;

        if ($course === null || ! $course->is_active || blank($course->slug)) {
            $fail(tkey('website.validation.course_cannot_be_published'));

            return;
        }

        $defaultLocale = app(LocaleManager::class)->defaultLocale();
        $content = app(TranslatableContentManager::class);
        $name = $course->getTranslations('name')[$defaultLocale]
            ?? $course->getTranslations('title')[$defaultLocale]
            ?? $course->title;

        if (
            ! $content->isMissingValue($name)
            && ((int) $course->price_cents > 0 || ! $content->isMissingValue($course->displayDescription($defaultLocale)))
        ) {
            return;
        }

        $fail(tkey('website.validation.course_cannot_be_published'));
    }
}
