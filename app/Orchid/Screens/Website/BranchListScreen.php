<?php

namespace App\Orchid\Screens\Website;

use App\Models\Branch;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class BranchListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->ordered()
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.branches.title');
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
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.branches.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('branches', [
                TD::make('name', tkey('website.branches.fields.name'))
                    ->render(fn (Branch $branch): string => $branch->displayName().' '.$this->seoWarning($branch->displaySeoTitle(), $branch->displaySeoDescription())),
                TD::make('city', tkey('website.branches.fields.city'))
                    ->render(fn (Branch $branch): string => $branch->displayCity()),
                TD::make('phone', tkey('website.branches.fields.phone'))
                    ->render(fn (Branch $branch): string => $branch->phone ?? '-'),
                TD::make('email', tkey('website.branches.fields.email'))
                    ->render(fn (Branch $branch): string => $branch->email ?? '-'),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (Branch $branch): string => $this->booleanBadge($branch->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('is_visible_on_site', tkey('website.admin.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (Branch $branch): string => $this->booleanBadge($branch->is_visible_on_site, 'website.admin.status.visible', 'website.admin.status.hidden')),
                TD::make('sort_order', tkey('website.admin.fields.sort_order'))
                    ->alignCenter()
                    ->render(fn (Branch $branch): string => (string) $branch->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Branch $branch): string => $this->branchActions($branch)),
            ]),
        ];
    }

    private function branchActions(Branch $branch): string
    {
        return implode(' ', [
            (string) Link::make(tkey('common.actions.edit'))
                ->icon('bs.pencil')
                ->route('platform.website.branches.edit', $branch),
            (string) Link::make(tkey('website.admin.actions.open_public_page'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.branches.show', ['branch' => $branch->slug])
                ->target('_blank'),
        ]);
    }
}
