<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Branch;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class BranchListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['students', 'instructors', 'vehicles', 'groups'])
                ->orderBy('sort_order')
                ->orderBy('city')
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
        return ['platform.operations.branches', 'website.manage_branches'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.branches.create_title'))
                ->icon('bs.plus-circle')
                ->route('platform.website.branches.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('branches', [
                TD::make('name', tkey('website.admin.branches.fields.name'))
                    ->render(fn (Branch $branch): string => $branch->displayName()),
                TD::make('city', tkey('website.admin.branches.fields.city'))
                    ->render(fn (Branch $branch): string => $branch->displayCity()),
                TD::make('address', tkey('website.admin.branches.fields.address'))
                    ->render(fn (Branch $branch): string => $branch->displayAddress()),
                TD::make('students_count', tkey('website.admin.branches.columns.students'))
                    ->render(fn (Branch $branch): string => (string) $branch->students_count)
                    ->alignCenter(),
                TD::make('instructors_count', tkey('website.admin.branches.columns.instructors'))
                    ->render(fn (Branch $branch): string => (string) $branch->instructors_count)
                    ->alignCenter(),
                TD::make('vehicles_count', tkey('website.admin.branches.columns.vehicles'))
                    ->render(fn (Branch $branch): string => (string) $branch->vehicles_count)
                    ->alignCenter(),
                TD::make('groups_count', tkey('website.admin.branches.columns.groups'))
                    ->render(fn (Branch $branch): string => (string) $branch->groups_count)
                    ->alignCenter(),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->render(fn (Branch $branch): string => $branch->is_active ? tkey('common.status.yes') : tkey('common.status.no'))
                    ->alignCenter(),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Branch $branch): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.branches.edit', $branch)),
            ]),
        ];
    }
}
