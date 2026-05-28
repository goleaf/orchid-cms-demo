<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\User;

class CreateStudentOnboardingTasksAction
{
    /**
     * @var array<string, int>
     */
    private const DUE_DAYS = [
        'verify_personal_data' => 1,
        'request_documents' => 1,
        'prepare_contract' => 2,
        'check_payment' => 2,
        'assign_group' => 3,
        'create_portal_access' => 3,
    ];

    /**
     * @var array<int, string>
     */
    private const DEFAULT_TASKS = [
        'verify_personal_data',
        'request_documents',
        'prepare_contract',
        'check_payment',
        'assign_group',
        'create_portal_access',
    ];

    /**
     * @return array<int, StudentTask>
     */
    public function handle(Student $student, ?User $createdBy = null, ?StudentEnrollment $enrollment = null, bool $allowDuplicates = false): array
    {
        $tasks = [];
        $existingTasks = $allowDuplicates
            ? collect()
            : $student->tasks()
                ->select(['id', 'student_id', 'enrollment_id', 'title_translations', 'description_translations', 'assigned_to_id', 'created_by_id', 'priority', 'status', 'due_at', 'completed_at', 'cancelled_at'])
                ->when(
                    $enrollment !== null,
                    fn ($query) => $query->where('enrollment_id', $enrollment->id),
                    fn ($query) => $query->whereNull('enrollment_id'),
                )
                ->get()
                ->keyBy(fn (StudentTask $task): string => $this->taskKey((array) $task->title_translations));

        foreach (self::DEFAULT_TASKS as $code) {
            $titleTranslations = $this->titleTranslations($code);
            $existingTask = $existingTasks->get($this->taskKey($titleTranslations));

            if ($existingTask instanceof StudentTask) {
                $tasks[] = $existingTask;

                continue;
            }

            $task = app(CreateStudentTaskAction::class)->handle(
                $student,
                $titleTranslations,
                $createdBy,
                now()->addDays(self::DUE_DAYS[$code]),
                in_array($code, ['request_documents', 'check_payment'], true) ? 'high' : 'normal',
                null,
                null,
                $enrollment,
            );

            $tasks[] = $task;
            $existingTasks->put($this->taskKey($titleTranslations), $task);
        }

        return $tasks;
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

    /**
     * @param  array<string, string|null>  $titleTranslations
     */
    private function taskKey(array $titleTranslations): string
    {
        return (string) ($titleTranslations['en'] ?? reset($titleTranslations) ?: '');
    }
}
