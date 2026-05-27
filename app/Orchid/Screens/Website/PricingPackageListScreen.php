<?php

namespace App\Orchid\Screens\Website;

use App\Models\PricingPackage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PricingPackageListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        return [
            'packages' => PricingPackage::query()
                ->forAdminList()
                ->with([
                    'course:id,title,title_translations,name_translations,slug',
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
        return ['website.manage_pricing'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.pricing.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('packages', [
                TD::make('name', tkey('website.pricing.fields.package'))
                    ->render(fn (PricingPackage $package): string => $package->displayName()),
                TD::make('course', tkey('website.pricing.fields.course'))
                    ->render(fn (PricingPackage $package): string => $package->course?->displayTitle() ?? '-'),
                TD::make('category', tkey('website.admin.pricing.fields.category'))
                    ->render(fn (PricingPackage $package): string => $package->category?->displayName() ?? '-'),
                TD::make('price', tkey('website.pricing.fields.price'))
                    ->render(fn (PricingPackage $package): string => $package->priceForHumans()),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $this->booleanBadge($package->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('is_visible_on_site', tkey('website.admin.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $this->booleanBadge($package->is_visible_on_site, 'website.admin.status.visible', 'website.admin.status.hidden')),
                TD::make('is_featured', tkey('website.admin.fields.is_featured'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => $this->booleanBadge($package->is_featured)),
                TD::make('sort_order', tkey('website.admin.fields.sort_order'))
                    ->alignCenter()
                    ->render(fn (PricingPackage $package): string => (string) $package->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (PricingPackage $package): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.pricing.edit', $package)),
            ]),
        ];
    }
}
