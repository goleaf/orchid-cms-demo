<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateBranchAction;
use App\Actions\HideBranchFromSiteAction;
use App\Actions\PublishBranchOnSiteAction;
use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
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

class BranchEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?Branch $branch = null;

    public function query(?Branch $branch = null): iterable
    {
        $branchModel = $branch?->exists
            ? $branch
            : new Branch([
                'slug' => '',
                'is_active' => true,
                'is_visible_on_site' => true,
                'is_indexable' => true,
                'sort_order' => 0,
            ]);

        $this->branch = $branchModel;

        return [
            'branch' => $branchModel,
            'id' => $branchModel->id,
            'code' => $branchModel->code,
            'slug' => $branchModel->slug,
            'phone' => $branchModel->phone,
            'email' => $branchModel->email,
            'latitude' => $branchModel->latitude,
            'longitude' => $branchModel->longitude,
            'map_url' => $branchModel->map_url,
            'image' => $branchModel->image,
            'canonical_url' => $branchModel->canonical_url,
            'open_graph_image' => $branchModel->open_graph_image,
            'is_active' => $branchModel->is_active,
            'is_visible_on_site' => $branchModel->is_visible_on_site,
            'is_indexable' => $branchModel->is_indexable,
            'sort_order' => $branchModel->sort_order,
            'name_translations' => $this->translations($branchModel, 'name', $branchModel->name),
            'country_translations' => $this->translations($branchModel, 'country', $branchModel->country),
            'city_translations' => $this->translations($branchModel, 'city', $branchModel->city),
            'address_translations' => $this->translations($branchModel, 'address', $branchModel->address),
            'description_translations' => $this->translations($branchModel, 'description', $branchModel->description),
            'working_hours_translations' => $this->translations($branchModel, 'working_hours', $branchModel->working_hours),
            'seo_title_translations' => $this->translations($branchModel, 'seo_title', $branchModel->seo_title),
            'seo_description_translations' => $this->translations($branchModel, 'seo_description', $branchModel->seo_description),
            'og_title_translations' => $this->translations($branchModel, 'og_title', $branchModel->og_title),
            'og_description_translations' => $this->translations($branchModel, 'og_description', $branchModel->og_description),
        ];
    }

    public function name(): ?string
    {
        return $this->branch?->exists
            ? tkey('website.admin.branches.edit_title')
            : tkey('website.admin.branches.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.branches.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_branches'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.branches'),
            Link::make(tkey('website.admin.actions.preview'))
                ->icon('bs.box-arrow-up-right')
                ->href($this->branch?->exists ? route('website.branches.show', ['branch' => $this->branch->slug]) : '#')
                ->target('_blank')
                ->canSee((bool) $this->branch?->exists),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
            Button::make(tkey('website.admin.actions.publish'))
                ->icon('bs.upload')
                ->method('publish')
                ->canSee((bool) $this->branch?->exists),
            Button::make(tkey('website.admin.actions.hide'))
                ->icon('bs.eye-slash')
                ->method('hide')
                ->canSee((bool) $this->branch?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Input::make('code')
                    ->title(tkey('website.admin.fields.code')),
                Input::make('slug')
                    ->title(tkey('website.seo.fields.slug'))
                    ->required(),
                Input::make('phone')
                    ->title(tkey('website.branches.fields.phone')),
                Input::make('email')
                    ->type('email')
                    ->title(tkey('website.branches.fields.email')),
                Input::make('latitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->title(tkey('website.admin.branches.fields.latitude')),
                Input::make('longitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->title(tkey('website.admin.branches.fields.longitude')),
                Input::make('map_url')
                    ->title(tkey('website.branches.fields.map')),
                Input::make('image')
                    ->title(tkey('website.admin.fields.image_path')),
                Input::make('canonical_url')
                    ->title(tkey('website.seo.fields.canonical_url')),
                Input::make('open_graph_image')
                    ->title(tkey('website.seo.fields.og_image')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_visible_on_site')
                    ->title(tkey('website.admin.fields.is_visible_on_site'))
                    ->options($this->booleanOptions()),
                Select::make('is_indexable')
                    ->title(tkey('website.seo.fields.is_indexable'))
                    ->options($this->booleanOptions()),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('name', 'website.branches.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::input('country', 'website.branches.fields.country', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
            ]),
            TranslatableFields::input('city', 'website.branches.fields.city', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
            ]),
            TranslatableFields::input('address', 'website.branches.fields.address', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('description', 'website.branches.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('working_hours', 'website.branches.fields.working_hours', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
            ]),
            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),
        ];
    }

    public function save(StoreBranchRequest $request, CreateOrUpdateBranchAction $save): RedirectResponse
    {
        $branch = $this->resolveScreenModel($request, 'branch', Branch::class);

        $save->handle($branch, $this->validatedPayload($request, [
            'name',
            'country',
            'city',
            'address',
            'description',
            'working_hours',
            'seo_title',
            'seo_description',
            'og_title',
            'og_description',
        ]));

        Toast::info(tkey('website.admin.branches.messages.saved'));

        return redirect()->route('platform.website.branches');
    }

    public function publish(PublishBranchOnSiteAction $publish): RedirectResponse
    {
        $branch = $this->branch?->exists ? $this->branch : $this->resolveScreenModel(request(), 'branch', Branch::class);

        if ($branch instanceof Branch && $branch->exists) {
            $publish->handle($branch);
            Toast::info(tkey('website.admin.branches.messages.published'));
        }

        return redirect()->route('platform.website.branches');
    }

    public function hide(HideBranchFromSiteAction $hide): RedirectResponse
    {
        $branch = $this->branch?->exists ? $this->branch : $this->resolveScreenModel(request(), 'branch', Branch::class);

        if ($branch instanceof Branch && $branch->exists) {
            $hide->handle($branch);
            Toast::info(tkey('website.admin.branches.messages.hidden'));
        }

        return redirect()->route('platform.website.branches');
    }
}
