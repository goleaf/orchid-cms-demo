<?php

namespace App\Actions;

use App\Models\ExamAdmission;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class ApproveExamAdmissionAction
{
    public function handle(ExamAdmission $admission, ?User $user = null): ExamAdmission
    {
        return app(ExamWorkflowService::class)->approveAdmission($admission, $user);
    }
}
