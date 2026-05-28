<?php

namespace App\Actions;

use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;
use Illuminate\Support\Collection;

class BuildExamAdmissionChecklistAction
{
    public function handle(
        ExamAdmission|StudentEnrollment $subject,
        ExamType|int|string|null $type = null,
        ?ExamSession $session = null,
        ?ExamAttempt $attempt = null,
        ?User $user = null,
    ): Collection {
        return app(ExamWorkflowService::class)->buildAdmissionChecklist($subject, $type, $session, $attempt, $user);
    }
}
