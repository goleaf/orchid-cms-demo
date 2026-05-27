<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\MarketingLead;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class LeadListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'leads' => MarketingLead::query()
                ->forLeadList()
                ->with([
                    'branch:id,name,city',
                    'marketingCampaign:id,name,channel',
                    'convertedStudentProfile:id,first_name,last_name',
                ])
                ->orderByDesc('created_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Marketing leads';
    }

    public function description(): ?string
    {
        return 'Inquiry pipeline from public website and campaigns into CRM students.';
    }

    public function permission(): iterable
    {
        return ['platform.marketing.leads'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('leads', [
                TD::make('name', 'Lead')
                    ->render(fn (MarketingLead $lead): string => $lead->fullName()),
                TD::make('contact', 'Contact')
                    ->render(fn (MarketingLead $lead): string => $lead->email ?: ($lead->phone ?? '-')),
                TD::make('campaign', 'Campaign')
                    ->render(fn (MarketingLead $lead): string => $lead->marketingCampaign?->name ?? '-'),
                TD::make('branch', 'Branch')
                    ->render(fn (MarketingLead $lead): string => $lead->branch?->name ?? '-'),
                TD::make('source', 'Source')
                    ->render(fn (MarketingLead $lead): string => str($lead->source)->replace('_', ' ')->title()->toString()),
                TD::make('license_category', 'Category')
                    ->render(fn (MarketingLead $lead): string => $lead->license_category ?? '-')
                    ->alignCenter(),
                TD::make('status', 'Status')
                    ->render(fn (MarketingLead $lead): string => str($lead->status->value)->replace('_', ' ')->title()->toString()),
                TD::make('converted', 'Converted')
                    ->render(fn (MarketingLead $lead): string => $lead->convertedStudentProfile?->fullName() ?? '-'),
            ]),
        ];
    }
}
