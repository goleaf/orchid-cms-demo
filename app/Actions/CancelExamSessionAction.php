<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class CancelExamSessionAction
{
    public function handle(ExamSession $session, ?User $user = null, ?string $reason = null): ExamSession
    {
        return app(ExamWorkflowService::class)->cancelSession($session, $user, $reason);
    }
}
