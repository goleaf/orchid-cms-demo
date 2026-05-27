<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\MarketingLead;
use App\Models\User;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CreateCallbackLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?Request $request = null): MarketingLead
    {
        $tracking = $request === null
            ? []
            : app(CaptureUtmDataAction::class)->handle($request, [
                'source' => 'callback',
                'form_name' => 'callback',
            ]);
        $data = array_merge($tracking, $data);
        $context = app(ResolveWebsiteCourseContextAction::class)->handle($data);
        $fullName = trim((string) ($data['full_name'] ?? trim((string) ($data['first_name'] ?? '').' '.(string) ($data['last_name'] ?? ''))));
        $firstName = filled($data['first_name'] ?? null)
            ? (string) $data['first_name']
            : trim(str($fullName)->before(' ')->toString());
        $lastName = filled($data['last_name'] ?? null)
            ? (string) $data['last_name']
            : (filled($fullName) ? trim(str($fullName)->after(' ')->toString()) : null);
        $manager = User::query()
            ->select(['id', 'name', 'email'])
            ->where('email', 'admin@example.com')
            ->first();
        $duplicate = app(DetectLeadDuplicateAction::class)->handle($data);

        $lead = MarketingLead::query()->create([
            'uuid' => (string) Str::uuid(),
            'marketing_campaign_id' => null,
            'responsible_manager_id' => $manager?->id,
            'assigned_by_user_id' => null,
            'assigned_at' => $manager !== null ? now() : null,
            'full_name' => filled($fullName) ? $fullName : null,
            'branch_id' => $context['branch_id'],
            'training_program_id' => $context['course_id'],
            'course_category_id' => $context['course_category_id'],
            'training_group_id' => null,
            'instructor_id' => null,
            'converted_student_profile_id' => null,
            'duplicate_of_id' => $duplicate?->id,
            'first_name' => $firstName ?: tkey('crm.leads.fallback.lead'),
            'last_name' => filled($lastName) ? $lastName : null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'messenger' => $data['messenger'] ?? $data['preferred_messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => 'callback',
            'status' => LeadStatus::New,
            'license_category' => null,
            'preferred_format' => null,
            'preferred_language' => $data['preferred_language'] ?? app()->getLocale(),
            'preferred_time' => $data['preferred_time'] ?? $data['callback_time'] ?? null,
            'budget_cents' => null,
            'is_hot' => false,
            'priority' => 'high',
            'lead_score' => 60,
            'next_follow_up_at' => now()->addMinutes(30),
            'last_status_changed_at' => now(),
            'privacy_accepted_at' => now(),
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'consent_text_version' => $data['consent_text_version'] ?? 'callback-v1',
            'message' => $data['message'] ?? $data['comment'] ?? null,
            'internal_comment' => null,
            'rejection_reason' => null,
            'lost_reason_code' => null,
            'crm_snapshot' => [
                'form' => 'callback',
                'captured_at' => now()->toIso8601String(),
            ],
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'referrer_url' => $data['referrer_url'] ?? null,
            'landing_page' => $data['landing_page'] ?? null,
            'form_page' => $data['form_page'] ?? null,
            'form_name' => 'callback',
            'locale' => $data['locale'] ?? app()->getLocale(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $manager,
            'created',
            tkey('crm.activities.titles.created'),
            tkey('website.callback.crm_comment'),
        );

        app(AddLeadCommentAction::class)->handle(
            $lead,
            $manager,
            tkey('website.callback.crm_comment'),
        );

        app(AddLeadCommunicationAction::class)->handle(
            $lead,
            $manager,
            'web_form',
            'inbound',
            tkey('website.callback.communication_subject'),
            $lead->message,
            [
                'source' => $lead->source,
                'form_name' => $lead->form_name,
                'utm_source' => $lead->utm_source,
                'utm_medium' => $lead->utm_medium,
                'utm_campaign' => $lead->utm_campaign,
            ],
        );

        $lead->statusHistories()->create([
            'user_id' => $manager?->id,
            'from_status' => null,
            'to_status' => LeadStatus::New,
            'reason' => tkey('website.callback.status_reason'),
            'changed_at' => now(),
        ]);

        if ($duplicate !== null) {
            app(RecordLeadActivityAction::class)->handle(
                $lead,
                $manager,
                'marked_duplicate',
                tkey('crm.activities.titles.possible_duplicate'),
                tkey('crm.activities.messages.possible_duplicate', ['id' => $duplicate->id]),
                null,
                (string) $duplicate->id,
            );
        }

        app(CreateLeadTaskAction::class)->handle(
            $lead->refresh(),
            $manager,
            tkey('website.callback.task_title', ['name' => $lead->fullName()]),
            $lead->next_follow_up_at,
            LeadTaskPriority::High,
            tkey('website.callback.task_note'),
        );

        $managers = User::query()
            ->select(['id', 'name', 'email'])
            ->when($manager !== null, fn ($query) => $query->whereKey($manager->id))
            ->limit(5)
            ->get();

        Notification::send($managers, new EnrollmentLeadSubmittedNotification($lead));

        return $lead;
    }
}
