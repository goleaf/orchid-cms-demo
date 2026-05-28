<?php

namespace App\Actions;

use App\Models\ExamAdmission;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class BlockExamAdmissionAction
{
    public function handle(ExamAdmission $admission, ?string $reason = null, ?User $user = null): ExamAdmission
    {
        return app(ExamWorkflowService::class)->blockAdmission($admission, $reason, $user);
    }
}
