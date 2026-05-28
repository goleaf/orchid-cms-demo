<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class CancelExamAttemptAction
{
    public function handle(ExamAttempt $attempt, ?User $user = null, ?string $reason = null): ExamAttempt
    {
        return app(ExamWorkflowService::class)->cancelAttempt($attempt, $user, $reason);
    }
}
