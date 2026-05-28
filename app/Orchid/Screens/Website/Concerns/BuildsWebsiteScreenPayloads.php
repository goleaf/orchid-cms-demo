<?php

namespace App\Orchid\Screens\Website\Concerns;

use App\Actions\MoveSortableOrderAction;
use App\Enums\CourseFormat;
use App\Enums\TransmissionType;
use App\Models\Branch;
use App\Models\Course;
use App\Models\SitePage;
use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

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
        return collect(CourseFormat::values())
            ->mapWithKeys(fn (string $format): array => [$format => tkey('website.courses.formats.'.$format)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function transmissionOptions(): array
    {
        return collect(TransmissionType::values())
            ->mapWithKeys(fn (string $transmission): array => [$transmission => tkey('website.transmissions.'.$transmission)])
            ->all();
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
     * @param  class-string<Model>  $modelClass
     */
    protected function applyOrderControlState(mixed $items, string $modelClass): void
    {
        if (! method_exists($items, 'getCollection')) {
            return;
        }

        $ids = $this->orderedIds($modelClass);
        $firstId = $ids->first();
        $lastId = $ids->last();

        $items->getCollection()->each(function (Model $model) use ($firstId, $lastId): void {
            $model->setAttribute('can_move_up', $model->getKey() !== $firstId);
            $model->setAttribute('can_move_down', $model->getKey() !== $lastId);
        });
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function moveSortable(
        Request $request,
        string $modelClass,
        string $routeName,
        MoveSortableOrderAction $move,
        string $direction,
    ): RedirectResponse {
        $model = $modelClass::query()->findOrFail($request->integer('id'));

        $move->handle($model, $direction);

        Toast::info(tkey('website.admin.messages.order_updated'));

        return redirect()->route($routeName);
    }

    protected function orderControls(Model $model): string
    {
        return implode(' ', [
            (string) Button::make(tkey('website.admin.actions.move_up'))
                ->icon('bs.arrow-up')
                ->method('moveUp')
                ->parameters(['id' => $model->getKey()])
                ->canSee((bool) $model->getAttribute('can_move_up')),
            (string) Button::make(tkey('website.admin.actions.move_down'))
                ->icon('bs.arrow-down')
                ->method('moveDown')
                ->parameters(['id' => $model->getKey()])
                ->canSee((bool) $model->getAttribute('can_move_down')),
        ]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Collection<int, mixed>
     */
    private function orderedIds(string $modelClass): Collection
    {
        /** @var Model $model */
        $model = new $modelClass;

        return $this->orderedQuery($modelClass)->pluck($model->getKeyName())->values();
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function orderedQuery(string $modelClass): Builder
    {
        /** @var Model $model */
        $model = new $modelClass;
        $query = $modelClass::query();

        if (method_exists($modelClass, 'scopeOrdered')) {
            return $query->ordered();
        }

        return $query->orderBy('sort_order')->orderBy($model->getKeyName());
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
