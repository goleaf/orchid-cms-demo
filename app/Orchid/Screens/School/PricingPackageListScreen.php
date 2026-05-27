<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\PricingPackage;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PricingPackageListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'packages' => PricingPackage::query()
                ->forAdminList()
                ->with([
                    'course' => fn ($query) => $query->forAcademyList(),
                    'category:id,name_translations,code,slug',
                ])
                ->ordered()
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.pricing.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.pricing.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_courses', 'platform.lms.programs'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.pricing.create_title'))
                ->icon('bs.plus-circle')
                ->route('platform.website.pricing.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('packages', [
                TD::make('name', tkey('website.admin.pricing.fields.name'))
                    ->render(fn (PricingPackage $package): string => $package->displayName()),
                TD::make('course', tkey('website.admin.pricing.fields.course'))
                    ->render(fn (PricingPackage $package): string => $package->course?->displayTitle()
                        ?? $package->category?->displayName()
                        ?? '-'),
                TD::make('price', tkey('website.admin.pricing.fields.price'))
                    ->render(fn (PricingPackage $package): string => $package->priceForHumans()),
                TD::make('is_featured', tkey('website.admin.pricing.fields.is_featured'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $package->is_featured ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('is_visible_on_site', tkey('website.admin.pricing.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $package->is_visible_on_site ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $package->is_active ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (PricingPackage $package): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.pricing.edit', $package)),
            ]),
        ];
    }
}
