<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class RemoveStudentFromExamSessionAction
{
    public function handle(ExamSession $session, Student|int $student, StudentEnrollment|int|null $enrollment = null, ?User $user = null): bool
    {
        return app(ExamWorkflowService::class)->removeStudentFromSession($session, $student, $enrollment, $user);
    }
}
