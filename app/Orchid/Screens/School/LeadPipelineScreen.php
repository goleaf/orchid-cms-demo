<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\GetLeadPipelineAction;
use App\Actions\MoveLeadToStatusAction;
use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadPipelineScreen extends Screen
{
    /**
     * @return array<string, mixed>
     */
    public function query(Request $request, GetLeadPipelineAction $pipeline): iterable
    {
        return $pipeline->handle($request->only([
            'manager_id',
            'source',
            'license_category',
            'branch_id',
            'hot',
            'overdue',
        ]));
    }

    public function name(): ?string
    {
        return tkey('crm.pipeline.title');
    }

    public function description(): ?string
    {
        return tkey('crm.pipeline.description');
    }

    public function permission(): iterable
    {
        return ['platform.marketing.pipeline'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.title'))
                ->icon('bs.list-ul')
                ->route('platform.marketing.leads'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.school.lead-pipeline'),
        ];
    }

    public function moveLead(Request $request, MoveLeadToStatusAction $moveLead): RedirectResponse
    {
        $data = $request->validate([
            'lead_id' => ['required', 'integer', 'exists:marketing_leads,id'],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead = MarketingLead::query()
            ->forCrmDetail()
            ->whereKey($data['lead_id'])
            ->firstOrFail();

        $moveLead->handle($lead, LeadStatus::from($data['status']), $request->user(), $data['reason'] ?? null);

        Toast::info(tkey('crm.pipeline.messages.lead_moved', [
            'status' => LeadStatus::from($data['status'])->label(),
        ]));

        return redirect()->back();
    }
}
