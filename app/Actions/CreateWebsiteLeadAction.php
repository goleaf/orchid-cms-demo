<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\Course;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CreateWebsiteLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $documents
     */
    public function handle(array $data, ?Request $request = null, array $documents = []): Lead
    {
        $tracking = $request === null
            ? []
            : app(CaptureUtmDataAction::class)->handle($request, [
                'source' => 'website',
                'form_name' => $data['form_name'] ?? 'enrollment',
            ]);
        $data = array_merge($tracking, $data);
        $context = app(ResolveWebsiteCourseContextAction::class)->handle($data);
        $data['phone'] = app(NormalizePhoneAction::class)->handle($data['phone'] ?? null);
        $fullName = trim((string) ($data['full_name'] ?? trim((string) ($data['first_name'] ?? '').' '.(string) ($data['last_name'] ?? ''))));
        $firstName = filled($data['first_name'] ?? null)
            ? (string) $data['first_name']
            : trim(str($fullName)->before(' ')->toString());
        $lastName = filled($data['last_name'] ?? null)
            ? (string) $data['last_name']
            : (filled($fullName) ? trim(str($fullName)->after(' ')->toString()) : null);
        $course = filled($context['course_id'])
            ? Course::query()
                ->select(['id', 'course_category_id', 'license_category'])
                ->whereKey($context['course_id'])
                ->first()
            : null;

        $manager = User::query()
            ->select(['id', 'name', 'email'])
            ->where('email', 'admin@example.com')
            ->first();
        $duplicate = app(DetectLeadDuplicateAction::class)->handle($data);
        $budgetCents = filled($data['budget_eur'] ?? null)
            ? (int) round(((float) $data['budget_eur']) * 100)
            : null;
        $isHot = $budgetCents !== null && $budgetCents >= 120000;

        $lead = Lead::query()->create([
            'uuid' => (string) Str::uuid(),
            'full_name' => filled($fullName) ? $fullName : null,
            'marketing_campaign_id' => null,
            'responsible_manager_id' => $manager?->id,
            'assigned_by_user_id' => null,
            'assigned_at' => $manager !== null ? now() : null,
            'branch_id' => $context['branch_id'],
            'training_program_id' => $context['course_id'],
            'course_category_id' => $context['course_category_id'] ?? $course?->course_category_id,
            'training_group_id' => $context['training_group_id'],
            'instructor_id' => $data['instructor_id'] ?? null,
            'converted_student_profile_id' => null,
            'duplicate_of_id' => $duplicate?->id,
            'first_name' => $firstName ?: tkey('crm.leads.fallback.lead'),
            'last_name' => filled($lastName) ? $lastName : null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'messenger' => $data['messenger'] ?? $data['preferred_messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => 'website',
            'status' => LeadStatus::New,
            'license_category' => $data['license_category'] ?? $course?->license_category,
            'preferred_format' => $data['preferred_format'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'budget_cents' => $budgetCents,
            'is_hot' => $isHot,
            'priority' => $isHot ? 'high' : 'normal',
            'lead_score' => $isHot ? 80 : 50,
            'next_follow_up_at' => now()->addMinutes(30),
            'last_status_changed_at' => now(),
            'privacy_accepted_at' => now(),
            'consent_accepted' => true,
            'consent_accepted_at' => now(),
            'consent_text_version' => $data['consent_text_version'] ?? 'public-application-v1',
            'message' => $data['message'] ?? null,
            'internal_comment' => null,
            'rejection_reason' => null,
            'lost_reason_code' => null,
            'crm_snapshot' => [
                'form' => 'public_website',
                'captured_at' => now()->toIso8601String(),
            ],
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'referrer_url' => $data['referrer_url'] ?? $data['referrer'] ?? null,
            'landing_page' => $data['landing_page'] ?? null,
            'form_page' => $data['form_page'] ?? null,
            'form_name' => $data['form_name'] ?? 'enrollment',
            'locale' => $data['locale'] ?? app()->getLocale(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $manager,
            'created',
            tkey('crm.activities.titles.created'),
            tkey('crm.activities.messages.public_enrollment_created'),
        );

        foreach ($documents as $document) {
            $lead->documents()->create([
                'original_name' => $document->getClientOriginalName(),
                'path' => $document->store('lead-documents'),
                'mime_type' => $document->getClientMimeType(),
                'size' => $document->getSize() ?: 0,
            ]);
        }

        app(AddLeadCommunicationAction::class)->handle(
            $lead,
            $manager,
            'web_form',
            'inbound',
            tkey('crm.communications.system_subjects.online_enrollment_request'),
            $lead->message,
            [
                'source' => $lead->source,
                'utm_source' => $lead->utm_source,
                'utm_medium' => $lead->utm_medium,
                'utm_campaign' => $lead->utm_campaign,
            ],
        );

        app(AddLeadCommentAction::class)->handle(
            $lead,
            $manager,
            tkey('crm.activities.messages.public_enrollment_created'),
        );

        $lead->statusHistories()->create([
            'user_id' => $manager?->id,
            'from_status' => null,
            'to_status' => LeadStatus::New,
            'reason' => tkey('crm.activities.reasons.public_application_received'),
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
            tkey('crm.tasks.system_titles.call_new_application'),
            $lead->next_follow_up_at,
            $lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal,
            tkey('crm.tasks.system_notes.new_public_lead_reminder'),
        );

        $managers = User::query()
            ->select(['id', 'name', 'email'])
            ->when($manager !== null, fn ($query) => $query->whereKey($manager->id))
            ->limit(5)
            ->get();

        Notification::send($managers, new EnrollmentLeadSubmittedNotification($lead));

        if (filled($lead->email)) {
            Notification::route('mail', $lead->email)
                ->notify(new EnrollmentLeadAutoReplyNotification($lead));
        }

        return $lead->refresh();
    }
}
