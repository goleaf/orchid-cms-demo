<?php

namespace Tests\Feature;

use App\Orchid\PlatformProvider;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamLocalizationPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class]);
    }

    public function test_requested_exam_translation_keys_exist_for_all_active_locales(): void
    {
        $keys = [
            'permissions.groups.exams',
            ...$this->prefixed('menu.exams.', [
                'sessions',
                'internal',
                'official',
                'attempts',
                'results',
                'retakes',
                'admissions',
                'settings',
            ]),
            ...$this->prefixed('exams.', [
                'sessions.title',
                'sessions.create_title',
                'sessions.edit_title',
                'attempts.title',
                'results.title',
                'retakes.title',
                'admissions.title',
            ]),
            ...$this->prefixed('exams.fields.', [
                'exam_number',
                'type',
                'status',
                'branch',
                'group',
                'student',
                'enrollment',
                'examiner',
                'vehicle',
                'classroom',
                'scheduled_at',
                'capacity',
                'location',
                'attempt_no',
                'score',
                'max_score',
                'passed',
                'no_show',
                'result_status',
                'block_reason',
                'checklist',
                'decided_at',
                'decided_by',
            ]),
            ...$this->prefixed('exams.actions.', [
                'create',
                'save',
                'schedule',
                'cancel',
                'add_student',
                'remove_student',
                'check_admission',
                'approve_admission',
                'block_admission',
                'start_attempt',
                'complete_attempt',
                'record_result',
                'mark_passed',
                'mark_failed',
                'create_retake',
                'export_csv',
            ]),
            ...$this->prefixed('exams.types.', [
                'internal_theory',
                'internal_practical',
                'official_theory_placeholder',
                'official_practical_placeholder',
            ]),
            ...$this->prefixed('exams.statuses.', [
                'draft',
                'scheduled',
                'open',
                'in_progress',
                'completed',
                'cancelled',
                'archived',
            ]),
            ...$this->prefixed('exams.attempt_statuses.', [
                'planned',
                'allowed',
                'blocked',
                'in_progress',
                'passed',
                'failed',
                'no_show',
                'cancelled',
                'archived',
            ]),
            ...$this->prefixed('exams.result_statuses.', [
                'pending',
                'passed',
                'failed',
                'needs_retake',
                'cancelled',
            ]),
            ...$this->prefixed('exams.validation.', [
                'exam_type_not_active',
                'invalid_status_transition',
                'capacity_exceeded',
                'student_cannot_join_exam',
                'enrollment_cannot_take_exam',
                'documents_required',
                'payment_required',
                'theory_hours_required',
                'practice_hours_required',
                'internal_exam_required',
                'attempt_cannot_start',
                'attempt_cannot_complete',
                'invalid_score',
                'retake_not_allowed',
            ]),
        ];

        foreach ($keys as $key) {
            foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
                $value = tkey($key, [], $locale);

                $this->assertNotSame($key, $value, $key.' '.$locale);
                $this->assertNotSame('', $value, $key.' '.$locale);
            }
        }
    }

    public function test_exam_permission_labels_are_registered_and_translated(): void
    {
        $permissions = [
            'exams.sessions.view',
            'exams.sessions.create',
            'exams.sessions.update',
            'exams.sessions.cancel',
            'exams.admissions.check',
            'exams.admissions.approve',
            'exams.admissions.block',
            'exams.attempts.view',
            'exams.attempts.create',
            'exams.attempts.start',
            'exams.attempts.complete',
            'exams.attempts.cancel',
            'exams.results.view',
            'exams.results.record',
            'exams.results.update',
            'exams.retakes.view',
            'exams.retakes.create',
            'exams.retakes.schedule',
            'exams.dictionaries.manage',
            'exams.export',
        ];

        $registered = collect((new PlatformProvider(app()))->permissions())
            ->flatMap(fn (object $group): array => $group->items)
            ->keyBy('slug');

        foreach ($permissions as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
            $this->assertTrue($registered->has($permission), $permission);
            $this->assertSame(tkey('permissions.'.$permission), $registered[$permission]['description']);
            $this->assertNotSame('permissions.'.$permission, $registered[$permission]['description']);
        }
    }

    /**
     * @param  array<int, string>  $suffixes
     * @return array<int, string>
     */
    private function prefixed(string $prefix, array $suffixes): array
    {
        return array_map(fn (string $suffix): string => $prefix.$suffix, $suffixes);
    }
}
