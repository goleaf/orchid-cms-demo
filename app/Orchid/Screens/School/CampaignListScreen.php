<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\MarketingCampaign;
use App\Support\LocalizedLabel;
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
        return tkey('operations.marketing_campaigns.title');
    }

    public function description(): ?string
    {
        return tkey('operations.marketing_campaigns.description');
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
                TD::make('name', tkey('operations.columns.campaign'))
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->name),
                TD::make('branch', tkey('operations.columns.branch'))
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->branch?->name ?? tkey('operations.empty.all_branches')),
                TD::make('channel', tkey('operations.columns.channel'))
                    ->render(fn (MarketingCampaign $campaign): string => LocalizedLabel::for('operations.campaign_channels', $campaign->channel)),
                TD::make('budget_cents', tkey('operations.columns.budget'))
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->budgetForHumans()),
                TD::make('leads_count', tkey('operations.columns.leads'))
                    ->render(fn (MarketingCampaign $campaign): string => (string) $campaign->leads_count)
                    ->alignCenter(),
                TD::make('starts_on', tkey('operations.columns.starts'))
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->starts_on?->toDateString() ?? '-'),
                TD::make('ends_on', tkey('operations.columns.ends'))
                    ->render(fn (MarketingCampaign $campaign): string => $campaign->ends_on?->toDateString() ?? '-'),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (MarketingCampaign $campaign): string => LocalizedLabel::for('operations.statuses.campaigns', $campaign->status)),
            ]),
        ];
    }
}
