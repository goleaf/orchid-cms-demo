<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdatePricingPackageAction;
use App\Http\Requests\StorePricingPackageRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PricingPackageEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?PricingPackage $package = null;

    /**
     * @var array<int, string>
     */
    private array $courses = [];

    /**
     * @var array<int, string>
     */
    private array $categories = [];

    public function query(?PricingPackage $pricingPackage = null): iterable
    {
        $packageModel = $pricingPackage?->exists
            ? $pricingPackage
            : new PricingPackage([
                'currency' => 'EUR',
                'is_active' => true,
                'is_visible_on_site' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ]);

        $this->package = $packageModel;
        $this->courses = Course::query()
            ->select(['id', 'title', 'title_translations', 'name_translations', 'slug', 'sort_order', 'is_active'])
            ->active()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Course $course): array => [$course->id => $course->displayTitle()])
            ->all();
        $this->categories = CourseCategory::query()
            ->select(['id', 'name_translations', 'code', 'slug', 'sort_order'])
            ->active()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        return [
            'package' => $packageModel,
            'id' => $packageModel->id,
            'course_id' => $packageModel->course_id,
            'course_category_id' => $packageModel->course_category_id,
            'code' => $packageModel->code,
            'slug' => $packageModel->slug,
            'price' => $packageModel->price !== null ? number_format((float) $packageModel->price, 2, '.', '') : null,
            'old_price' => $packageModel->old_price !== null ? number_format((float) $packageModel->old_price, 2, '.', '') : null,
            'currency' => $packageModel->currency ?: 'EUR',
            'theory_hours' => $packageModel->theory_hours,
            'practice_hours' => $packageModel->practice_hours,
            'is_active' => $packageModel->is_active,
            'is_visible_on_site' => $packageModel->is_visible_on_site,
            'is_featured' => $packageModel->is_featured,
            'sort_order' => $packageModel->sort_order,
            'name_translations' => $this->translations($packageModel, 'name'),
            'description_translations' => $this->translations($packageModel, 'description'),
            'features_translations' => $this->featureTranslations($packageModel),
        ];
    }

    public function name(): ?string
    {
        return $this->package?->exists
            ? tkey('website.admin.pricing.edit_title')
            : tkey('website.admin.pricing.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.pricing.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_pricing'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.pricing'),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Select::make('course_id')
                    ->title(tkey('website.pricing.fields.course'))
                    ->empty(tkey('website.admin.pricing.empty.no_course'), '')
                    ->options($this->courses),
                Select::make('course_category_id')
                    ->title(tkey('website.admin.pricing.fields.category'))
                    ->empty(tkey('website.admin.pricing.empty.no_category'), '')
                    ->options($this->categories),
                Input::make('code')
                    ->title(tkey('website.admin.fields.code')),
                Input::make('slug')
                    ->title(tkey('website.seo.fields.slug')),
                Input::make('price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.pricing.fields.price')),
                Input::make('old_price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.courses.fields.old_price')),
                Input::make('currency')
                    ->title(tkey('website.admin.pricing.fields.currency'))
                    ->maxlength(3),
                Input::make('theory_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.pricing.fields.theory_hours')),
                Input::make('practice_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.pricing.fields.practice_hours')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_visible_on_site')
                    ->title(tkey('website.admin.fields.is_visible_on_site'))
                    ->options($this->booleanOptions()),
                Select::make('is_featured')
                    ->title(tkey('website.admin.fields.is_featured'))
                    ->options($this->booleanOptions()),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('name', 'website.pricing.fields.package', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('description', 'website.admin.pricing.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('features', 'website.pricing.fields.features', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 5,
            ]),
        ];
    }

    public function save(StorePricingPackageRequest $request, CreateOrUpdatePricingPackageAction $save): RedirectResponse
    {
        $package = $this->resolveScreenModel($request, 'pricingPackage', PricingPackage::class);

        $save->handle($package, $this->validatedPayload($request, [
            'name',
            'description',
            'features',
        ]));

        Toast::info(tkey('website.admin.pricing.messages.saved'));

        return redirect()->route('platform.website.pricing');
    }

    /**
     * @return array<string, mixed>
     */
    private function featureTranslations(PricingPackage $package): array
    {
        $translations = $package->getTranslations('features');

        if ($translations === []) {
            return $this->translations($package, 'features');
        }

        return collect($translations)
            ->map(fn (mixed $value): mixed => is_array($value)
                ? collect($value)->filter(fn (mixed $feature): bool => filled($feature))->implode("\n")
                : $value)
            ->all();
    }
}
