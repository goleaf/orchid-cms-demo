<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\GetLeadPipelineAction;
use App\Actions\ChangeLeadStatusAction;
use App\Http\Requests\Marketing\LeadPipelineMoveRequest;
use App\Models\MarketingLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'training_program_id',
            'license_category',
            'branch_id',
            'only_my',
            'hot',
            'overdue',
            'created_from',
            'created_to',
        ]) + [
            'current_user_id' => $request->user()?->id,
        ]);
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
        return ['crm.pipeline.view', 'platform.marketing.pipeline'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.title'))
                ->icon('bs.list-ul')
                ->route($this->leadIndexRoute()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.school.lead-pipeline'),
        ];
    }

    public function moveLead(LeadPipelineMoveRequest $request, ChangeLeadStatusAction $moveLead): RedirectResponse
    {
        $lead = MarketingLead::query()
            ->forCrmDetail()
            ->whereKey($request->leadId())
            ->firstOrFail();

        $moveLead->handle($lead, $request->status(), $request->user(), $request->reason());

        Toast::info(tkey('crm.pipeline.messages.lead_moved', [
            'status' => $request->status()->label(),
        ]));

        return redirect()->back();
    }

    private function leadIndexRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads'
            : 'platform.crm.leads';
    }
}
