<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\MarketingLead;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Notifications\EnrollmentLeadAutoReplyNotification;
use App\Notifications\EnrollmentLeadSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;

class CreateEnrollmentLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $documents
     */
    public function handle(array $data, array $documents = []): MarketingLead
    {
        $program = TrainingProgram::query()
            ->select(['id', 'license_category'])
            ->whereKey($data['training_program_id'])
            ->firstOrFail();

        $manager = User::query()
            ->select(['id', 'name', 'email'])
            ->where('email', 'admin@example.com')
            ->first();

        $lead = MarketingLead::query()->create([
            'marketing_campaign_id' => null,
            'responsible_manager_id' => $manager?->id,
            'branch_id' => $data['branch_id'],
            'training_program_id' => $data['training_program_id'],
            'training_group_id' => $data['training_group_id'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'converted_student_profile_id' => null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'messenger' => $data['messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'] ?? 'website',
            'status' => LeadStatus::New,
            'license_category' => $program->license_category,
            'preferred_format' => $data['preferred_format'],
            'preferred_language' => $data['preferred_language'],
            'preferred_time' => $data['preferred_time'] ?? null,
            'budget_cents' => filled($data['budget_eur'] ?? null)
                ? (int) round(((float) $data['budget_eur']) * 100)
                : null,
            'is_hot' => filled($data['budget_eur'] ?? null) && (float) $data['budget_eur'] >= 1200,
            'next_follow_up_at' => now()->addHour(),
            'last_status_changed_at' => now(),
            'privacy_accepted_at' => now(),
            'message' => $data['message'] ?? null,
            'rejection_reason' => null,
            'crm_snapshot' => [
                'form' => 'public_enrollment',
                'captured_at' => now()->toIso8601String(),
            ],
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'referrer_url' => $data['referrer_url'] ?? null,
        ]);

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
            'Online enrollment request',
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
            'Lead created automatically from public enrollment form.',
        );

        $lead->statusHistories()->create([
            'user_id' => $manager?->id,
            'from_status' => null,
            'to_status' => LeadStatus::New,
            'reason' => 'Public application received.',
            'changed_at' => now(),
        ]);

        app(CreateLeadTaskAction::class)->handle(
            $lead->refresh(),
            $manager,
            'Call new application',
            $lead->next_follow_up_at,
            $lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal,
            'Automatic reminder for a new public website lead.',
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

        return $lead;
    }
}
