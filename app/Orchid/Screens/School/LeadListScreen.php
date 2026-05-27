<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\MarketingLead;
use Orchid\Screen\Actions\Link;
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
                    'responsibleManager:id,name',
                    'trainingProgram:id,title',
                    'convertedStudentProfile:id,first_name,last_name',
                ])
                ->withCount(['comments', 'communications', 'documents'])
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
                    ->render(fn (MarketingLead $lead): string => (string) Link::make($lead->fullName())
                        ->route('platform.marketing.leads.edit', $lead)),
                TD::make('contact', 'Contact')
                    ->render(fn (MarketingLead $lead): string => $lead->email ?: ($lead->phone ?? '-')),
                TD::make('messenger', 'Messenger')
                    ->render(fn (MarketingLead $lead): string => $lead->messenger ?? '-'),
                TD::make('city', 'City')
                    ->render(fn (MarketingLead $lead): string => $lead->city ?? '-'),
                TD::make('campaign', 'Campaign')
                    ->render(fn (MarketingLead $lead): string => $lead->marketingCampaign?->name ?? '-'),
                TD::make('branch', 'Branch')
                    ->render(fn (MarketingLead $lead): string => $lead->branch?->name ?? '-'),
                TD::make('course', 'Course')
                    ->render(fn (MarketingLead $lead): string => $lead->trainingProgram?->title ?? '-'),
                TD::make('source', 'Source')
                    ->render(fn (MarketingLead $lead): string => str($lead->source)->replace('_', ' ')->title()->toString()),
                TD::make('license_category', 'Category')
                    ->render(fn (MarketingLead $lead): string => $lead->license_category ?? '-')
                    ->alignCenter(),
                TD::make('manager', 'Manager')
                    ->render(fn (MarketingLead $lead): string => $lead->responsibleManager?->name ?? '-'),
                TD::make('budget', 'Budget')
                    ->render(fn (MarketingLead $lead): string => $lead->budgetForHumans()),
                TD::make('status', 'Status')
                    ->render(fn (MarketingLead $lead): string => $lead->status->label()),
                TD::make('activity', 'Activity')
                    ->render(fn (MarketingLead $lead): string => $lead->communications_count.' comms / '.$lead->comments_count.' notes / '.$lead->documents_count.' docs'),
                TD::make('converted', 'Converted')
                    ->render(fn (MarketingLead $lead): string => $lead->convertedStudentProfile?->fullName() ?? '-'),
            ]),
        ];
    }
}
