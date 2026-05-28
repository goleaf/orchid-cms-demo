<?php

namespace App\Actions;

use App\Services\Exams\ExamWorkflowService;

class GenerateExamNumberAction
{
    public function handle(mixed $scheduledAt = null): string
    {
        return app(ExamWorkflowService::class)->generateExamNumber($scheduledAt);
    }
}
