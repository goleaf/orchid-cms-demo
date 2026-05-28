<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\ExamRetake;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class ScheduleExamRetakeAction
{
    public function handle(ExamRetake|ExamAttempt $retake, ExamAttempt|int|null $newAttempt = null, ?User $user = null, mixed $plannedAt = null): ExamRetake
    {
        $service = app(ExamWorkflowService::class);

        if ($retake instanceof ExamAttempt) {
            $retake = $service->createRetakeRecord($retake, [
                'new_attempt_id' => $newAttempt instanceof ExamAttempt ? $newAttempt->id : $newAttempt,
                'planned_at' => $plannedAt,
            ], $user);
        }

        return $service->scheduleRetake($retake, $newAttempt, $user, $plannedAt);
    }
}
