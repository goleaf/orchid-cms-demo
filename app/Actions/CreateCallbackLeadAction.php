<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\MarketingLead;
use App\Models\User;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CreateCallbackLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): MarketingLead
    {
        $manager = User::query()
            ->select(['id', 'name', 'email'])
            ->where('email', 'admin@example.com')
            ->first();

        $lead = MarketingLead::query()->create([
            'uuid' => (string) Str::uuid(),
            'marketing_campaign_id' => null,
            'responsible_manager_id' => $manager?->id,
            'branch_id' => $data['branch_id'] ?? null,
            'training_program_id' => $data['training_program_id'] ?? null,
            'training_group_id' => null,
            'instructor_id' => null,
            'converted_student_profile_id' => null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'messenger' => $data['messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'] ?? 'callback',
            'status' => LeadStatus::New,
            'license_category' => null,
            'preferred_format' => null,
            'preferred_language' => $data['preferred_language'] ?? app()->getLocale(),
            'preferred_time' => $data['preferred_time'] ?? null,
            'budget_cents' => null,
            'is_hot' => false,
            'next_follow_up_at' => now()->addMinutes(30),
            'last_status_changed_at' => now(),
            'privacy_accepted_at' => now(),
            'message' => $data['message'] ?? null,
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
            'form_name' => $data['form_name'] ?? 'callback',
            'locale' => $data['locale'] ?? app()->getLocale(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

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
