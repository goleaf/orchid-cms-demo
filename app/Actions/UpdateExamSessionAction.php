<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class UpdateExamSessionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamSession $session, array $data, ?User $user = null): ExamSession
    {
        return app(ExamWorkflowService::class)->updateSession($session, $data, $user);
    }
}
