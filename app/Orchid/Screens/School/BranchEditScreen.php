<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SaveBranchAction;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
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
    public ?Branch $branch = null;

    public function query(?Branch $branch = null): iterable
    {
        $branchModel = $branch?->exists
            ? $branch
            : new Branch([
                'slug' => '',
                'is_active' => true,
                'sort_order' => 0,
            ]);
        $this->branch = $branchModel;

        return [
            'branch' => $branchModel,
            'name_translations' => $this->translations($branchModel, 'name', $branchModel->name),
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
        return ['platform.operations.branches', 'website.manage_branches'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.branches'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('branch.id')->type('hidden'),
                Input::make('branch.slug')
                    ->title(tkey('website.admin.fields.slug'))
                    ->required(),
                Input::make('branch.phone')
                    ->title(tkey('crm.leads.fields.phone')),
                Input::make('branch.email')
                    ->type('email')
                    ->title(tkey('crm.leads.fields.email')),
                Input::make('branch.latitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->title(tkey('website.admin.branches.fields.latitude')),
                Input::make('branch.longitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->title(tkey('website.admin.branches.fields.longitude')),
                Input::make('branch.canonical_url')
                    ->title(tkey('website.admin.fields.canonical_url')),
                Input::make('branch.open_graph_image')
                    ->title(tkey('website.admin.fields.open_graph_image')),
                Input::make('branch.sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
                Select::make('branch.is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('name', 'website.admin.branches.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::input('city', 'website.admin.branches.fields.city', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::input('address', 'website.admin.branches.fields.address', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::textarea('description', 'website.admin.branches.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('working_hours', 'website.admin.branches.fields.working_hours', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
            ]),
            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),
        ];
    }

    public function save(BranchRequest $request, SaveBranchAction $save): RedirectResponse
    {
        $branch = filled($request->input('branch.id'))
            ? Branch::query()->findOrFail($request->integer('branch.id'))
            : new Branch;

        $save->handle($branch, $request->branchData());

        Toast::info(tkey('website.admin.branches.messages.saved'));

        return redirect()->route('platform.website.branches');
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(Branch $branch, string $field, mixed $fallback): array
    {
        return $branch->getTranslations($field) ?: ['ru' => $fallback];
    }
}
