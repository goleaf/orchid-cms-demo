<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class RecordExamResultAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamAttempt $attempt, array $data, ?User $user = null): ExamResult
    {
        return app(ExamWorkflowService::class)->recordResult($attempt, $data, $user);
    }
}
