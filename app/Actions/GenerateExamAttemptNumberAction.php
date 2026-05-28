<?php

namespace App\Actions;

use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Services\Exams\ExamWorkflowService;

class GenerateExamAttemptNumberAction
{
    public function handle(StudentEnrollment $enrollment, ExamType|int|string|null $type = null): int
    {
        return app(ExamWorkflowService::class)->generateAttemptNumber($enrollment, $type);
    }
}
