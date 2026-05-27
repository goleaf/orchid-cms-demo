<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SavePricingPackageAction;
use App\Http\Requests\PricingPackageRequest;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use App\Models\TrainingProgram;
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
    /**
     * @var PricingPackage|null
     */
    public $package = null;

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
                'sort_order' => 0,
            ]);
        $this->package = $packageModel;

        $this->courses = TrainingProgram::query()
            ->forAcademyList()
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();
        $this->categories = CourseCategory::query()
            ->select(['id', 'name_translations', 'code', 'slug', 'sort_order'])
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(fn (CourseCategory $category): array => [$category->id => $category->displayName()])
            ->all();

        return [
            'package' => $packageModel,
            'package.price' => $packageModel->price !== null ? number_format((float) $packageModel->price, 2, '.', '') : null,
            'package.old_price' => $packageModel->old_price !== null ? number_format((float) $packageModel->old_price, 2, '.', '') : null,
            'name_translations' => $packageModel->getTranslations('name') ?: ['ru' => null],
            'description_translations' => $packageModel->getTranslations('description') ?: ['ru' => null],
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
        return ['website.manage_pricing', 'website.manage_courses', 'platform.lms.programs'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.pricing'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('package.id')->type('hidden'),
                Input::make('package.slug')
                    ->title(tkey('website.admin.fields.slug'))
                    ->required(),
                Input::make('package.code')
                    ->title(tkey('website.admin.pricing.fields.code')),
                Select::make('package.course_id')
                    ->title(tkey('website.admin.pricing.fields.course'))
                    ->options($this->courses)
                    ->empty(tkey('website.admin.pricing.empty.no_course')),
                Select::make('package.course_category_id')
                    ->title(tkey('website.admin.pricing.fields.category'))
                    ->options($this->categories)
                    ->empty(tkey('website.admin.pricing.empty.no_category')),
                Input::make('package.price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.admin.pricing.fields.price'))
                    ->required(),
                Input::make('package.old_price')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.admin.pricing.fields.old_price')),
                Input::make('package.currency')
                    ->title(tkey('website.admin.pricing.fields.currency'))
                    ->maxlength(3)
                    ->required(),
                Input::make('package.theory_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.admin.pricing.fields.theory_hours')),
                Input::make('package.practice_hours')
                    ->type('number')
                    ->step('0.25')
                    ->title(tkey('website.admin.pricing.fields.practice_hours')),
                Input::make('package.sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
                Select::make('package.is_featured')
                    ->title(tkey('website.admin.pricing.fields.is_featured'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
                Select::make('package.is_visible_on_site')
                    ->title(tkey('website.admin.pricing.fields.is_visible_on_site'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
                Select::make('package.is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('name', 'website.admin.pricing.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::textarea('description', 'website.admin.pricing.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('features', 'website.admin.pricing.fields.features', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 5,
            ]),
        ];
    }

    public function save(PricingPackageRequest $request, SavePricingPackageAction $save): RedirectResponse
    {
        $package = filled($request->input('package.id'))
            ? PricingPackage::query()->findOrFail($request->integer('package.id'))
            : new PricingPackage;

        $save->handle($package, $request->packageData());

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
            return ['ru' => null];
        }

        return collect($translations)
            ->map(function (mixed $value): mixed {
                if (is_array($value)) {
                    return collect($value)
                        ->filter(fn (mixed $feature): bool => filled($feature))
                        ->implode("\n");
                }

                return $value;
            })
            ->all();
    }
}
