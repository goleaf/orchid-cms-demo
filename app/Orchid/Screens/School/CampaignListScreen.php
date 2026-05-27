<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\MarketingCampaign;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CampaignListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'campaigns' => MarketingCampaign::query()
                ->forCampaignList()
                ->with('branch:id,name,city')
                ->withCount('leads')
                ->orderByDesc('starts_on')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Marketing campaigns';
    }

    public function description(): ?string
    {
        return 'Acquisition channels, budgets, UTM tracking, and generated demand.';
    }

    public function permission(): iterable
    {
        return ['platform.marketing.campaigns'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('campaigns', [
                TD::make('name', 'Campaign')
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->name),
                TD::make('branch', 'Branch')
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->branch?->name ?? 'All branches'),
                TD::make('channel', 'Channel')
                    ->render(fn (MarketingCampaign $campaign): string => str($campaign->channel)->replace('_', ' ')->title()->toString()),
                TD::make('budget_cents', 'Budget')
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->budgetForHumans()),
                TD::make('leads_count', 'Leads')
                    ->render(fn (MarketingCampaign $campaign): string => (string) $campaign->leads_count)
                    ->alignCenter(),
                TD::make('starts_on', 'Starts')
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->starts_on?->toDateString() ?? '-'),
                TD::make('ends_on', 'Ends')
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->ends_on?->toDateString() ?? '-'),
                TD::make('status', 'Status')
                    ->render(fn (MarketingCampaign $campaign): string => str($campaign->status->value)->replace('_', ' ')->title()->toString()),
            ]),
        ];
    }
}
