<?php

namespace App\Actions;

use App\Models\ExamSession;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;
use Illuminate\Support\Collection;

class RecheckExamSessionAdmissionsAction
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(ExamSession $session, ?User $user = null): Collection
    {
        return app(ExamWorkflowService::class)->recheckSessionAdmissions($session, $user);
    }
}
