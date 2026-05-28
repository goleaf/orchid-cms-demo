<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class CreateExamSessionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): ExamSession
    {
        return app(ExamWorkflowService::class)->createSession($data, $user);
    }
}
