<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\Lead;
use App\Models\LeadStatus as LeadStatusDictionary;
use App\Models\User;
use Illuminate\Support\Str;

class CreateLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $tagIds
     */
    public function handle(array $data, ?User $user = null, array $tagIds = []): Lead
    {
        $phone = app(NormalizeLeadPhoneAction::class)->handle($data['phone'] ?? null);
        $fullName = trim((string) ($data['full_name'] ?? trim((string) ($data['first_name'] ?? '').' '.(string) ($data['last_name'] ?? ''))));
        $firstName = filled($data['first_name'] ?? null)
            ? (string) $data['first_name']
            : trim(str($fullName)->before(' ')->toString());
        $lastName = filled($data['last_name'] ?? null)
            ? (string) $data['last_name']
            : (filled($fullName) ? trim(str($fullName)->after(' ')->toString()) : null);
        $status = $this->statusValue($data['status'] ?? null);
        $duplicate = app(DetectLeadDuplicateAction::class)->handle([
            ...$data,
            'phone' => $phone,
        ]);

        $lead = Lead::query()->create([
            'uuid' => (string) ($data['uuid'] ?? Str::uuid()),
            'lead_number' => $data['lead_number'] ?? app(GenerateLeadNumberAction::class)->handle(),
            'full_name' => filled($fullName) ? $fullName : null,
            'marketing_campaign_id' => $data['marketing_campaign_id'] ?? null,
            'responsible_manager_id' => $data['responsible_manager_id'] ?? $data['manager_id'] ?? null,
            'assigned_by_user_id' => filled($data['responsible_manager_id'] ?? $data['manager_id'] ?? null) ? $user?->id : null,
            'assigned_at' => filled($data['responsible_manager_id'] ?? $data['manager_id'] ?? null) ? now() : null,
            'branch_id' => $data['branch_id'] ?? null,
            'training_program_id' => $data['training_program_id'] ?? $data['course_id'] ?? null,
            'course_category_id' => $data['course_category_id'] ?? null,
            'training_group_id' => $data['training_group_id'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'converted_student_profile_id' => null,
            'converted_enrollment_id' => null,
            'created_by_user_id' => $data['created_by_user_id'] ?? $user?->id,
            'updated_by_user_id' => $data['updated_by_user_id'] ?? $user?->id,
            'duplicate_of_id' => $data['duplicate_of_id'] ?? $duplicate?->id,
            'first_name' => $firstName ?: tkey('crm.leads.fallback.lead'),
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => filled($lastName) ? $lastName : null,
            'email' => $data['email'] ?? null,
            'phone' => $phone,
            'messenger' => $data['messenger'] ?? $data['preferred_messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'] ?? 'phone',
            'status' => $status,
            'license_category' => $data['license_category'] ?? null,
            'preferred_format' => $data['preferred_format'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? $data['preferred_training_language'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'desired_start_date' => $data['desired_start_date'] ?? null,
            'preferred_gearbox' => $data['preferred_gearbox'] ?? null,
            'budget_cents' => $this->budgetCents($data['budget_eur'] ?? $data['budget'] ?? null),
            'is_hot' => (bool) ($data['is_hot'] ?? false),
            'priority' => $data['priority'] ?? 'normal',
            'lead_score' => (int) ($data['lead_score'] ?? 0),
            'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
            'last_status_changed_at' => now(),
            'privacy_accepted_at' => $data['privacy_accepted_at'] ?? null,
            'consent_accepted' => (bool) ($data['consent_accepted'] ?? false),
            'consent_accepted_at' => $data['consent_accepted_at'] ?? ((bool) ($data['consent_accepted'] ?? false) ? now() : null),
            'consent_text_version' => $data['consent_text_version'] ?? null,
            'contacted_at' => $data['contacted_at'] ?? null,
            'last_contacted_at' => $data['last_contacted_at'] ?? null,
            'converted_at' => null,
            'closed_at' => null,
            'message' => $data['message'] ?? $data['comment'] ?? null,
            'internal_comment' => $data['internal_comment'] ?? null,
            'rejection_reason' => null,
            'lost_reason_code' => null,
            'crm_snapshot' => $data['crm_snapshot'] ?? null,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'referrer_url' => $data['referrer_url'] ?? $data['referrer'] ?? null,
            'landing_page' => $data['landing_page'] ?? null,
            'form_page' => $data['form_page'] ?? null,
            'form_name' => $data['form_name'] ?? null,
            'locale' => $data['locale'] ?? app()->getLocale(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        if ($tagIds !== []) {
            $lead->tags()->sync($tagIds);
        }

        $lead->statusHistories()->create([
            'user_id' => $user?->id,
            'from_status' => null,
            'to_status' => $status,
            'reason' => tkey('crm.activities.reasons.manual_lead_created'),
            'changed_at' => now(),
        ]);

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            'created_manually',
            tkey('crm.activities.titles.created'),
            tkey('crm.activities.messages.manual_lead_created'),
        );

        if ($duplicate !== null) {
            app(RecordLeadActivityAction::class)->handle(
                $lead->refresh(),
                $user,
                'marked_duplicate',
                tkey('crm.activities.titles.possible_duplicate'),
                tkey('crm.activities.messages.possible_duplicate', ['id' => $duplicate->id]),
                null,
                (string) $duplicate->id,
            );
        }

        if ((bool) config('crm.leads.manual_first_task_enabled', true)) {
            app(CreateLeadTaskAction::class)->handle(
                $lead->refresh(),
                $user,
                tkey('crm.tasks.defaults.contact_new_website_lead'),
                now()->addMinutes(max(1, (int) config('crm.leads.manual_first_task_due_minutes', 30))),
                LeadTaskPriority::Normal,
                tkey('crm.tasks.system_notes.new_public_lead_reminder'),
            );
        }

        return $lead->refresh();
    }

    private function statusValue(mixed $status): string
    {
        if ($status instanceof LeadStatus) {
            return $status->value;
        }

        if (filled($status)) {
            return (string) $status;
        }

        return LeadStatusDictionary::query()
            ->where('is_default', true)
            ->value('code') ?: LeadStatus::New->value;
    }

    private function budgetCents(mixed $budget): ?int
    {
        return filled($budget) ? (int) round(((float) $budget) * 100) : null;
    }
}
