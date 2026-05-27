<?php

namespace App\Rules;

use App\Models\Branch;
use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicBranchCanBePublishedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $branch = filled($value) ? Branch::query()->find($value) : null;

        if ($branch === null || ! $branch->is_active || blank($branch->slug)) {
            $fail(tkey('website.validation.branch_cannot_be_published'));

            return;
        }

        $defaultLocale = app(LocaleManager::class)->defaultLocale();
        $content = app(TranslatableContentManager::class);
        $name = $branch->getTranslations('name')[$defaultLocale] ?? $branch->name;

        if (
            ! $content->isMissingValue($name)
            && (filled($branch->phone) || filled($branch->email))
        ) {
            return;
        }

        $fail(tkey('website.validation.branch_cannot_be_published'));
    }
}
