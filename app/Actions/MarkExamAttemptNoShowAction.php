<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class MarkExamAttemptNoShowAction
{
    public function handle(ExamAttempt $attempt, ?User $user = null): ExamAttempt
    {
        return app(ExamWorkflowService::class)->markAttemptNoShow($attempt, $user);
    }
}
