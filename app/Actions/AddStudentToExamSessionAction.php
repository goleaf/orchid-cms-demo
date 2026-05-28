<?php

namespace App\Actions;

use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class AddStudentToExamSessionAction
{
    public function handle(
        ExamSession $session,
        Student|int $student,
        StudentEnrollment|int $enrollment,
        ?User $user = null,
        bool $allowOverbooking = false,
        bool $admitted = true,
        ?string $blockReason = null,
    ): ExamParticipant {
        return app(ExamWorkflowService::class)->addStudentToSession(
            $session,
            $student,
            $enrollment,
            $user,
            $allowOverbooking,
            $admitted,
            $blockReason,
        );
    }
}
