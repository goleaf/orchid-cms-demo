<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\User;

class CreateStudentOnboardingTasksAction
{
    /**
     * @return array<int, StudentTask>
     */
    public function handle(Student $student, ?User $createdBy = null, ?StudentEnrollment $enrollment = null): array
    {
        $tasks = [];

        foreach ($this->defaultTasks() as $index => $code) {
            $tasks[] = app(CreateStudentTaskAction::class)->handle(
                $student,
                $this->titleTranslations($code),
                $createdBy,
                now()->addDays($index + 1),
                in_array($code, ['request_documents', 'check_payment'], true) ? 'high' : 'normal',
                null,
                null,
                $enrollment,
            );
        }

        return $tasks;
    }

    /**
     * @return array<int, string>
     */
    private function defaultTasks(): array
    {
        return [
            'verify_personal_data',
            'request_documents',
            'prepare_contract',
            'check_payment',
            'assign_group',
            'create_portal_access',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function titleTranslations(string $code): array
    {
        $key = 'students.tasks.defaults.'.$code;

        return [
            'ru' => tkey($key, [], 'ru'),
            'en' => tkey($key, [], 'en'),
            'lt' => tkey($key, [], 'lt'),
            'pl' => tkey($key, [], 'pl'),
        ];
    }
}
