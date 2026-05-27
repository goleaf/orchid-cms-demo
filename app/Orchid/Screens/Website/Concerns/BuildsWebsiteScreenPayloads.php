<?php

namespace App\Orchid\Screens\Website\Concerns;

use App\Models\Branch;
use App\Models\Course;
use App\Models\SitePage;
use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

trait BuildsWebsiteScreenPayloads
{
    /**
     * @param  array<int, string>  $translationFields
     * @return array<string, mixed>
     */
    protected function validatedPayload(FormRequest $request, array $translationFields = []): array
    {
        $payload = $request->validated();

        if ($translationFields === []) {
            return $payload;
        }

        return [
            ...$payload,
            ...app(TranslatableContentManager::class)->extract($request, $translationFields),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function translations(?Model $model, string $field, mixed $fallback = null): array
    {
        if ($model !== null && method_exists($model, 'getTranslations')) {
            $translations = $model->getTranslations($field);

            if ($translations !== []) {
                return $translations;
            }
        }

        return [app(LocaleManager::class)->defaultLocale() => $fallback];
    }

    /**
     * @return array<int, string>
     */
    protected function booleanOptions(): array
    {
        return [
            1 => tkey('common.status.yes'),
            0 => tkey('common.status.no'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function courseFormatOptions(): array
    {
        return [
            'offline' => tkey('website.courses.formats.offline'),
            'online' => tkey('website.courses.formats.online'),
            'hybrid' => tkey('website.courses.formats.hybrid'),
            'individual' => tkey('website.courses.formats.individual'),
            'group' => tkey('website.courses.formats.group'),
            'mixed' => tkey('website.courses.formats.mixed'),
        ];
    }

    protected function booleanBadge(bool $value, ?string $trueKey = null, ?string $falseKey = null): string
    {
        return $this->badge(
            $value ? ($trueKey ?? 'common.status.yes') : ($falseKey ?? 'common.status.no'),
            $value ? 'success' : 'secondary',
        );
    }

    protected function badge(string $labelKey, string $color = 'secondary'): string
    {
        return '<span class="badge bg-'.$color.'">'.e(tkey($labelKey)).'</span>';
    }

    protected function seoWarning(?string $title, ?string $description): string
    {
        if (filled($title) && filled($description)) {
            return '';
        }

        return '<span class="badge bg-warning text-dark">'.e(tkey('website.admin.seo.warnings.missing_metadata')).'</span>';
    }

    protected function publicPageUrl(Model $model): ?string
    {
        if ($model instanceof SitePage) {
            return match ($model->type) {
                'home' => route('website.home'),
                'pricing' => route('website.pricing'),
                'contacts' => route('website.contacts'),
                'thank_you' => route('website.thank_you'),
                default => filled($model->slug) ? route('website.pages.show', $model) : null,
            };
        }

        if ($model instanceof Course && filled($model->slug)) {
            return route('website.courses.show', $model);
        }

        if ($model instanceof Branch && filled($model->slug)) {
            return route('website.branches.show', ['branch' => $model->slug]);
        }

        return null;
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return TModel|null
     */
    protected function resolveScreenModel(Request $request, string $routeKey, string $modelClass, string $routeColumn = 'id'): ?Model
    {
        $routeValue = $request->route($routeKey);

        if ($routeValue instanceof Model && is_a($routeValue, $modelClass)) {
            return $routeValue;
        }

        $id = $request->input('id') ?? $request->input($routeKey.'.id');

        if (filled($id)) {
            return $modelClass::query()->whereKey($id)->first();
        }

        if (filled($routeValue)) {
            return $modelClass::query()->where($routeColumn, (string) $routeValue)->first();
        }

        return null;
    }
}
