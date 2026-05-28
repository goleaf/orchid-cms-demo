<?php

namespace App\Actions;

use App\Models\ExamAdmission;
use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class CheckExamAdmissionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(StudentEnrollment $enrollment, ExamType|int|string $type, array $data = [], ?User $user = null): ExamAdmission
    {
        return app(ExamWorkflowService::class)->checkAdmission($enrollment, $type, $data, $user);
    }
}
