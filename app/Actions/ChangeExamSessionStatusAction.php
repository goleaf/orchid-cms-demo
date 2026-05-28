<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\ExamStatus;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class ChangeExamSessionStatusAction
{
    public function handle(ExamSession $session, ExamStatus|int|string $status, ?User $user = null, bool $allowOverride = false): ExamSession
    {
        return app(ExamWorkflowService::class)->changeSessionStatus($session, $status, $user, $allowOverride);
    }
}
