<?php

declare(strict_types=1);

namespace App\Orchid\Support;

use App\Services\LocaleManager;
use App\Services\TranslatableContentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layout;
use Orchid\Support\Facades\Layout as OrchidLayout;

class TranslatableFields
{
    public static function input(string $field, string $labelKey, array $options = []): Layout
    {
        return self::tabs([
            self::definition('input', $field, $labelKey, $options),
        ], self::titleKey($options));
    }

    public static function textarea(string $field, string $labelKey, array $options = []): Layout
    {
        return self::tabs([
            self::definition('textarea', $field, $labelKey, $options),
        ], self::titleKey($options));
    }

    public static function quill(string $field, string $labelKey, array $options = []): Layout
    {
        return self::tabs([
            self::definition('quill', $field, $labelKey, $options),
        ], self::titleKey($options));
    }

    public static function switch(string $field, string $labelKey, array $options = []): Layout
    {
        return self::tabs([
            self::definition('switch', $field, $labelKey, $options),
        ], self::titleKey($options));
    }

    public static function switcher(string $field, string $labelKey, array $options = []): Layout
    {
        return self::switch($field, $labelKey, $options);
    }

    public static function checkbox(string $field, string $labelKey, array $options = []): Layout
    {
        return self::tabs([
            self::definition('checkbox', $field, $labelKey, $options),
        ], self::titleKey($options));
    }

    public static function seo(array $options = []): Layout
    {
        $fields = [
            self::definition('input', 'seo_title', 'translatable.seo.fields.seo_title', [
                'maxlength' => 255,
            ]),
            self::definition('textarea', 'seo_description', 'translatable.seo.fields.seo_description', [
                'rows' => 3,
                'maxlength' => 500,
            ]),
            self::definition('input', 'og_title', 'translatable.seo.fields.og_title', [
                'maxlength' => 255,
            ]),
            self::definition('textarea', 'og_description', 'translatable.seo.fields.og_description', [
                'rows' => 3,
                'maxlength' => 500,
            ]),
        ];

        if ($options['keywords'] ?? true) {
            $fields[] = self::definition('input', 'seo_keywords', 'translatable.seo.fields.seo_keywords', [
                'maxlength' => 500,
            ]);
        }

        return self::tabs($fields, $options['title_key'] ?? $options['title'] ?? 'translatable.seo.title');
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    public static function tabs(array $fields, ?string $titleKey = null): Layout
    {
        $locales = app(LocaleManager::class);
        $defaultLocale = $locales->defaultLocale();
        $tabs = self::activeLanguages()
            ->mapWithKeys(function (mixed $language) use ($fields, $defaultLocale): array {
                $code = (string) $language->code;
                $isDefault = $code === $defaultLocale;
                $rows = [];

                foreach ($fields as $definition) {
                    if (! $isDefault && ($definition['copy'] ?? true)) {
                        $rows[] = self::copyDefaultSwitch($definition['field'], $language);
                    }

                    $rows[] = self::control($definition, $language, $isDefault);
                }

                return [
                    self::tabTitle($language, $isDefault) => OrchidLayout::rows($rows),
                ];
            })
            ->all();

        $layout = OrchidLayout::tabs($tabs);

        return $titleKey === null
            ? $layout
            : OrchidLayout::block($layout)->title(tkey($titleKey));
    }

    /**
     * @return array<string, mixed>
     */
    private static function definition(string $type, string $field, string $labelKey, array $options): array
    {
        return array_merge($options, [
            'type' => $type,
            'field' => app(TranslatableContentManager::class)->baseField($field),
            'attribute' => app(TranslatableContentManager::class)->translationAttribute($field),
            'label_key' => $labelKey,
        ]);
    }

    private static function control(array $definition, mixed $language, bool $isDefault): Field
    {
        $code = (string) $language->code;
        $name = $definition['attribute'].'.'.$code;

        $field = match ($definition['type']) {
            'textarea' => TextArea::make($name)->rows((int) ($definition['rows'] ?? 4)),
            'quill' => Quill::make($name)->height($definition['height'] ?? '280px'),
            'switch' => Switcher::make($name)->sendTrueOrFalse(),
            'checkbox' => CheckBox::make($name)->sendTrueOrFalse(),
            default => Input::make($name)->type($definition['input_type'] ?? 'text'),
        };

        $field->title(tkey((string) $definition['label_key']));

        if (isset($definition['maxlength']) && in_array($definition['type'], ['input', 'textarea'], true)) {
            $field->maxlength((int) $definition['maxlength']);
        }

        if (isset($definition['help_key'])) {
            $field->help(tkey((string) $definition['help_key']));
        }

        if ($definition['required'] ?? false) {
            $field->required();
        }

        return self::withMissingWarning($field, $isDefault);
    }

    private static function copyDefaultSwitch(string $field, mixed $language): Switcher
    {
        return Switcher::make(TranslatableContentManager::COPY_KEY.'.'.$field.'.'.$language->code)
            ->sendTrueOrFalse()
            ->title(tkey('translatable.actions.copy_default_value'))
            ->help(tkey('translatable.help.copy_default_on_save'));
    }

    private static function withMissingWarning(Field $field, bool $isDefault): Field
    {
        return $field->addBeforeRender(function () use ($isDefault): void {
            if (! app(TranslatableContentManager::class)->isMissingValue($this->get('value'))) {
                return;
            }

            $message = $isDefault
                ? tkey('translatable.warnings.default_missing')
                : tkey('translatable.warnings.missing_translation');
            $help = $this->get('help');

            $this->set('help', filled($help) ? trim($help.' '.$message) : $message);
        });
    }

    private static function tabTitle(mixed $language, bool $isDefault): string
    {
        $title = e($language->native_name ?: $language->name ?: Str::upper((string) $language->code));

        if (! $isDefault) {
            return $title;
        }

        return $title.' <span class="badge bg-primary ms-1">'.e(tkey('translatable.badges.default')).'</span>';
    }

    /**
     * @return Collection<int, mixed>
     */
    private static function activeLanguages(): Collection
    {
        return app(LocaleManager::class)->activeLanguages();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private static function titleKey(array $options): ?string
    {
        return $options['title_key'] ?? $options['title'] ?? null;
    }
}
