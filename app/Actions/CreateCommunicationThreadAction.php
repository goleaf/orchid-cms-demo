<?php

namespace App\Actions;

use App\Models\CommunicationThread;
use App\Models\Lead;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentProfile;
use App\Support\Notifications\NotificationTargetResolver;

class CreateCommunicationThreadAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): CommunicationThread
    {
        $target = app(NotificationTargetResolver::class)->resolve(
            is_string($data['target_type'] ?? null) ? $data['target_type'] : null,
            $data['target_id'] ?? null,
        );

        if ($target instanceof Student || $target instanceof StudentProfile) {
            $data['student_id'] = $data['student_id'] ?? $target->id;
        }

        if ($target instanceof Lead || $target instanceof MarketingLead) {
            $data['lead_id'] = $data['lead_id'] ?? $target->id;
        }

        return CommunicationThread::query()->create([
            'thread_number' => $data['thread_number'] ?? null,
            'subject' => $data['subject'] ?? null,
            'target_type' => $target?->getMorphClass() ?? $data['target_type'],
            'target_id' => $target?->getKey() ?? $data['target_id'],
            'student_id' => $data['student_id'] ?? null,
            'lead_id' => $data['lead_id'] ?? null,
            'status' => $data['status'] ?? CommunicationThread::STATUS_OPEN,
            'metadata' => $data['metadata'] ?? null,
        ])->refresh();
    }
}
