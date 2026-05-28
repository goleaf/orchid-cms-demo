<?php

namespace App\Actions;

use App\Models\LeadSource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportStudentsCsvAction
{
    /**
     * @param  Builder<Student>  $query
     *
     * @throws AuthorizationException
     */
    public function handle(Builder $query, ?User $user = null): StreamedResponse
    {
        if (! ($user?->hasAccess('students.export') ?? false)) {
            throw new AuthorizationException(tkey('students.validation.export_not_allowed'));
        }

        $includeCrmSource = $user->hasAccess('students.view_crm_source');
        $includeMarketing = $user->hasAnyAccess([
            'students.view_marketing',
            'crm.leads.view_marketing',
        ]);
        $filename = 'students-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query, $includeCrmSource, $includeMarketing): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, $this->headings($includeCrmSource, $includeMarketing));

            $sourceLabels = LeadSource::translatedLabels();
            $exportQuery = $this->prepareQuery(clone $query, $includeCrmSource, $includeMarketing);

            $exportQuery
                ->reorder('id')
                ->chunkById(200, function (Collection $students) use ($output, $sourceLabels, $includeCrmSource, $includeMarketing): void {
                    foreach ($students as $student) {
                        $this->writeRow($output, $student, $sourceLabels, $includeCrmSource, $includeMarketing);
                    }
                }, 'id');

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    private function prepareQuery(Builder $query, bool $includeCrmSource, bool $includeMarketing): Builder
    {
        $query
            ->select([
                'id',
                'uuid',
                'student_number',
                'branch_id',
                'full_name',
                'first_name',
                'middle_name',
                'last_name',
                'phone',
                'email',
                'status',
                'manager_id',
                'source_lead_id',
                'source_label',
                'created_at',
            ])
            ->with([
                'manager:id,name',
                'currentEnrollment' => fn ($query) => $query
                    ->select([
                        'enrollments.id',
                        'enrollments.uuid',
                        'enrollments.enrollment_number',
                        'enrollments.student_profile_id',
                        'enrollments.training_program_id',
                        'enrollments.branch_id',
                        'enrollments.training_group_id',
                        'enrollments.status',
                        'enrollments.start_date',
                        'enrollments.planned_end_date',
                        'enrollments.training_language',
                        'enrollments.format',
                        'enrollments.gearbox_type',
                        'enrollments.payment_status',
                    ])
                    ->with([
                        'trainingProgram:id,title,title_translations,license_category',
                        'branch:id,name,name_translations,city,city_translations',
                        'trainingGroup:id,name,name_translations,code',
                    ]),
            ]);

        if ($includeCrmSource || $includeMarketing) {
            $sourceLeadColumns = ['id', 'source'];

            if ($includeMarketing) {
                $sourceLeadColumns = [
                    ...$sourceLeadColumns,
                    'utm_source',
                    'utm_medium',
                    'utm_campaign',
                    'landing_page',
                    'form_page',
                ];
            }

            $query->with(['sourceLead:'.implode(',', $sourceLeadColumns)]);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function headings(bool $includeCrmSource, bool $includeMarketing): array
    {
        $headings = [
            tkey('students.fields.id'),
            tkey('students.fields.uuid'),
            tkey('students.fields.student_number'),
            tkey('students.fields.full_name'),
            tkey('students.fields.phone'),
            tkey('students.fields.email'),
            tkey('students.fields.status'),
            tkey('students.fields.current_course'),
            tkey('students.fields.current_branch'),
            tkey('students.fields.current_group'),
            tkey('students.enrollments.fields.status'),
            tkey('students.fields.manager'),
            tkey('students.fields.created_at'),
        ];

        if ($includeCrmSource) {
            $headings = [
                ...$headings,
                tkey('students.fields.source_lead'),
                tkey('students.fields.source_label'),
            ];
        }

        $headings = [
            ...$headings,
            tkey('students.enrollments.fields.enrollment_number'),
            tkey('students.enrollments.fields.start_date'),
            tkey('students.enrollments.fields.planned_end_date'),
            tkey('students.enrollments.fields.training_language'),
            tkey('students.enrollments.fields.format'),
            tkey('students.enrollments.fields.gearbox_type'),
            tkey('students.enrollments.fields.payment_status'),
        ];

        if ($includeMarketing) {
            $headings = [
                ...$headings,
                tkey('crm.leads.fields.source'),
                tkey('crm.leads.fields.utm_source'),
                tkey('crm.leads.fields.utm_medium'),
                tkey('crm.leads.fields.utm_campaign'),
                tkey('crm.leads.fields.landing_page'),
                tkey('crm.leads.fields.form_page'),
            ];
        }

        return $headings;
    }

    /**
     * @param  array<string, string>  $sourceLabels
     */
    private function writeRow(mixed $output, Student $student, array $sourceLabels, bool $includeCrmSource, bool $includeMarketing): void
    {
        $enrollment = $student->current_enrollment;
        $sourceLead = ($includeCrmSource || $includeMarketing) ? $student->sourceLead : null;

        $row = [
            $student->id,
            $student->uuid,
            $student->student_number,
            $student->display_name,
            $student->phone,
            $student->email,
            $student->status !== null ? tkey('students.statuses.'.$student->status->value) : null,
            $enrollment?->trainingProgram?->displayTitle(),
            $enrollment?->branch?->displayName(),
            $enrollment?->trainingGroup?->displayName(),
            $enrollment?->status !== null ? tkey('students.enrollments.statuses.'.$enrollment->status->value) : null,
            $student->manager?->name,
            $student->created_at?->format('Y-m-d H:i:s'),
        ];

        if ($includeCrmSource) {
            $row = [
                ...$row,
                $student->source_lead_id,
                $student->source_label,
            ];
        }

        $row = [
            ...$row,
            $enrollment?->enrollment_number,
            $enrollment?->start_date?->format('Y-m-d'),
            $enrollment?->planned_end_date?->format('Y-m-d'),
            $enrollment?->training_language,
            $enrollment?->format,
            $enrollment?->gearbox_type,
            $enrollment?->payment_status,
        ];

        if ($includeMarketing) {
            $source = $sourceLead?->source;
            $row = [
                ...$row,
                $source !== null ? ($sourceLabels[$source] ?? LeadSource::translatedLabel($source)) : null,
                $sourceLead?->utm_source,
                $sourceLead?->utm_medium,
                $sourceLead?->utm_campaign,
                $sourceLead?->landing_page,
                $sourceLead?->form_page,
            ];
        }

        fputcsv($output, $row);
    }
}
