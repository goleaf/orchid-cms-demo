<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class MarkExamPassedAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamAttempt $attempt, array $data = [], ?User $user = null): ExamAttempt
    {
        return app(ExamWorkflowService::class)->markPassed($attempt, $data, $user);
    }
}
