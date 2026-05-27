<?php

namespace App\Rules;

use App\Models\SitePage;
use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class PublishedPageRequirementRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $pageId = data_get($this->data, 'page.id') ?? data_get($this->data, 'id');
        $page = filled($pageId)
            ? SitePage::query()->find($pageId)
            : null;
        $titleTranslations = data_get($this->data, 'title_translations', $page?->title_translations ?? []);
        $contentTranslations = data_get($this->data, 'content_translations', $page?->content_translations ?? []);
        $defaultLocale = app(LocaleManager::class)->defaultLocale();
        $content = app(TranslatableContentManager::class);

        if (
            filled(data_get($this->data, 'page.slug') ?? data_get($this->data, 'slug') ?? $page?->slug)
            && ! $content->isMissingValue($titleTranslations[$defaultLocale] ?? null)
            && ! $content->isMissingValue($contentTranslations[$defaultLocale] ?? null)
        ) {
            return;
        }

        $fail(tkey('website.validation.page_cannot_be_published'));
    }
}
