<?php

namespace App\Actions;

use App\Models\LeadSource;
use App\Models\MarketingLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportMarketingLeadsCsvAction
{
    /**
     * @param  Builder<MarketingLead>  $query
     */
    public function handle(Builder $query, bool $includeMarketing = true): StreamedResponse
    {
        $filename = 'crm-leads-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query, $includeMarketing): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, $this->headings($includeMarketing));

            $sourceLabels = LeadSource::translatedLabels();
            $exportQuery = $this->prepareQuery(clone $query, $includeMarketing);

            $exportQuery
                ->reorder('id')
                ->chunkById(200, function (Collection $leads) use ($output, $sourceLabels, $includeMarketing): void {
                    foreach ($leads as $lead) {
                        $this->writeRow($output, $lead, $sourceLabels, $includeMarketing);
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Builder<MarketingLead>  $query
     * @return Builder<MarketingLead>
     */
    private function prepareQuery(Builder $query, bool $includeMarketing): Builder
    {
        $query
            ->addSelect([
                'id',
                'uuid',
                'lead_number',
                'responsible_manager_id',
                'branch_id',
                'training_program_id',
                'training_group_id',
                'full_name',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'phone',
                'source',
                'status',
                'priority',
                'lead_score',
                'created_at',
                'last_contacted_at',
                'next_follow_up_at',
                'closed_at',
                'converted_at',
                'message',
                'internal_comment',
            ])
            ->with([
                'responsibleManager:id,name',
                'trainingProgram:id,title,title_translations,name_translations,license_category',
                'branch:id,name,name_translations,city,city_translations',
                'trainingGroup:id,name,name_translations,code,group_number',
            ]);

        if ($includeMarketing) {
            $query->addSelect([
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'referrer_url',
                'landing_page',
                'form_page',
                'form_name',
                'locale',
                'ip_address',
                'user_agent',
            ]);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function headings(bool $includeMarketing): array
    {
        $headings = [
            tkey('crm.leads.fields.id'),
            tkey('crm.leads.fields.uuid'),
            tkey('crm.leads.fields.lead_number'),
            tkey('crm.leads.columns.full_name'),
            tkey('crm.leads.columns.phone'),
            tkey('crm.leads.columns.email'),
            tkey('crm.leads.columns.status'),
            tkey('crm.leads.columns.source'),
            tkey('crm.leads.columns.manager'),
            tkey('crm.leads.columns.course'),
            tkey('crm.leads.columns.branch'),
            tkey('crm.leads.columns.training_group'),
            tkey('crm.leads.fields.priority'),
            tkey('crm.leads.fields.lead_score'),
            tkey('crm.leads.fields.created_at'),
            tkey('crm.leads.fields.last_contacted_at'),
            tkey('crm.leads.fields.next_follow_up_at'),
            tkey('crm.leads.fields.closed_at'),
            tkey('crm.leads.fields.converted_at'),
            tkey('crm.leads.fields.comment'),
            tkey('crm.leads.fields.internal_comment'),
        ];

        if ($includeMarketing) {
            $headings = [
                ...$headings,
                tkey('crm.leads.fields.utm_source'),
                tkey('crm.leads.fields.utm_medium'),
                tkey('crm.leads.fields.utm_campaign'),
                tkey('crm.leads.fields.utm_content'),
                tkey('crm.leads.fields.utm_term'),
                tkey('crm.leads.fields.referrer'),
                tkey('crm.leads.fields.landing_page'),
                tkey('crm.leads.fields.form_page'),
                tkey('crm.leads.fields.form_name'),
                tkey('crm.leads.fields.locale'),
                tkey('crm.leads.fields.ip_address'),
                tkey('crm.leads.fields.user_agent'),
            ];
        }

        return $headings;
    }

    /**
     * @param  array<string, string>  $sourceLabels
     */
    private function writeRow(mixed $output, MarketingLead $lead, array $sourceLabels, bool $includeMarketing): void
    {
        $row = [
            $lead->id,
            $lead->uuid,
            $lead->lead_number,
            $lead->fullName(),
            $lead->phone,
            $lead->email,
            $lead->status->label(),
            $sourceLabels[$lead->source] ?? LeadSource::translatedLabel($lead->source),
            $lead->responsibleManager?->name,
            $lead->trainingProgram?->displayTitle(),
            $lead->branch?->displayName(),
            $lead->trainingGroup?->displayName(),
            $lead->priority,
            $lead->lead_score,
            $lead->created_at?->format('Y-m-d H:i:s'),
            $lead->last_contacted_at?->format('Y-m-d H:i:s'),
            $lead->next_follow_up_at?->format('Y-m-d H:i:s'),
            $lead->closed_at?->format('Y-m-d H:i:s'),
            $lead->converted_at?->format('Y-m-d H:i:s'),
            $lead->message,
            $lead->internal_comment,
        ];

        if ($includeMarketing) {
            $row = [
                ...$row,
                $lead->utm_source,
                $lead->utm_medium,
                $lead->utm_campaign,
                $lead->utm_content,
                $lead->utm_term,
                $lead->referrer_url,
                $lead->landing_page,
                $lead->form_page,
                $lead->form_name,
                $lead->locale,
                $lead->ip_address,
                $lead->user_agent,
            ];
        }

        fputcsv($output, $row);
    }
}
