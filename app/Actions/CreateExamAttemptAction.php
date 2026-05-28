<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class CreateExamAttemptAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamSession $session, Student|int $student, StudentEnrollment|int $enrollment, array $data = [], ?User $user = null): ExamAttempt
    {
        return app(ExamWorkflowService::class)->createAttempt($session, $student, $enrollment, $data, $user);
    }
}
