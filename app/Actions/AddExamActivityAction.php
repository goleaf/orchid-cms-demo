<?php

namespace App\Actions;

use App\Models\ExamActivity;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;

class AddExamActivityAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): ExamActivity
    {
        return app(ExamWorkflowService::class)->activity($data, $user);
    }
}
