<?php

namespace Tests\Feature;

use App\Enums\ExamAttemptStatus as LegacyExamAttemptStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Models\ExamActivity;
use App\Models\ExamAdmissionRule;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus;
use App\Models\ExamChecklistItem;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamResultStatus;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\ExamStatus;
use App\Models\ExamType;
use App\Models\TranslationString;
use Database\Seeders\ExamAdmissionRuleSeeder;
use Database\Seeders\ExamAttemptStatusSeeder;
use Database\Seeders\ExamResultStatusSeeder;
use Database\Seeders\ExamSeeder;
use Database\Seeders\ExamStatusSeeder;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\ExamTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamFactoriesSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_factories_create_valid_records_with_required_states(): void
    {
        $internalTheory = ExamType::factory()->internalTheory()->create();
        $internalPractical = ExamType::factory()->internalPractical()->create();
        $officialTheory = ExamType::factory()->officialTheory()->create();
        $officialPractical = ExamType::factory()->officialPractical()->create();
        $inactiveType = ExamType::factory()->inactive()->create();
        $translatedType = ExamType::factory()->translated()->create(['code' => 'translated_exam_type']);

        $this->assertTrue($internalTheory->is_internal && $internalTheory->is_theory);
        $this->assertTrue($internalPractical->is_internal && $internalPractical->is_practical);
        $this->assertTrue($officialTheory->is_official && $officialTheory->is_theory);
        $this->assertTrue($officialPractical->is_official && $officialPractical->is_practical);
        $this->assertFalse($inactiveType->is_active);
        $this->assertSame('Translated exam type', $translatedType->displayName('en'));

        $this->assertSame('draft', ExamStatus::factory()->default()->make()->code);
        $this->assertSame('completed', ExamStatus::factory()->final()->make()->code);
        foreach (['draft', 'scheduled', 'open', 'inProgress', 'completed', 'cancelled', 'archived', 'translated'] as $state) {
            $status = ExamStatus::factory()->{$state}()->create();
            $this->assertNotNull($status->id);
        }

        foreach (['planned', 'allowed', 'blocked', 'inProgress', 'passed', 'failed', 'noShow', 'cancelled', 'archived', 'translated'] as $state) {
            $status = ExamAttemptStatus::factory()->{$state}()->create();
            $this->assertNotNull($status->id);
        }

        foreach (['pending', 'passed', 'failed', 'needsRetake', 'cancelled', 'translated'] as $state) {
            $status = ExamResultStatus::factory()->{$state}()->create();
            $this->assertNotNull($status->id);
        }

        $rule = ExamAdmissionRule::factory()->forExamType($internalPractical)->create();
        $session = ExamSession::factory()
            ->officialPractical()
            ->completed()
            ->withGroup()
            ->withExaminer()
            ->withVehicle()
            ->translated()
            ->create();
        $participant = ExamParticipant::factory()->create(['exam_session_id' => $session->id]);
        $attempt = ExamAttempt::factory()->secondAttempt()->noShow()->create(['exam_session_id' => $session->id]);
        $failedResultStatus = ExamResultStatus::query()->where('code', 'failed')->firstOrFail();
        $result = ExamResult::factory()->create([
            'attempt_id' => $attempt->id,
            'result_status_id' => $failedResultStatus->id,
            'score' => 45,
            'max_score' => 100,
            'passed' => false,
        ]);
        $retake = ExamRetake::factory()->create(['previous_attempt_id' => $attempt->id]);
        $checklistItem = ExamChecklistItem::factory()->forAttempt($attempt)->passed()->create();
        $activity = ExamActivity::factory()->forAttempt($attempt)->create();

        $this->assertTrue($rule->examType->is($internalPractical));
        $this->assertSame(LegacyExamType::StatePractical, $session->exam_type);
        $this->assertSame(LegacyExamSessionStatus::Completed, $session->status);
        $this->assertNotNull($session->fresh()->group_id);
        $this->assertTrue($participant->session->is($session));
        $this->assertSame(LegacyExamAttemptStatus::NoShow, $attempt->status);
        $this->assertTrue($attempt->no_show);
        $this->assertFalse($result->passed);
        $this->assertTrue($retake->previousAttempt->is($attempt));
        $this->assertSame('passed', $checklistItem->status);
        $this->assertTrue($activity->attemptAlias->is($attempt));
    }

    public function test_exam_seeders_are_idempotent(): void
    {
        $seeders = [
            ExamTypeSeeder::class,
            ExamStatusSeeder::class,
            ExamAttemptStatusSeeder::class,
            ExamResultStatusSeeder::class,
            ExamAdmissionRuleSeeder::class,
            ExamTranslationSeeder::class,
            ExamSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }

        $counts = $this->examSeedCounts();

        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }

        $this->assertSame($counts, $this->examSeedCounts());
    }

    public function test_default_exam_types_statuses_and_translations_exist(): void
    {
        app()->setLocale('en');

        $this->seed(ExamSeeder::class);

        foreach (['internal_theory', 'internal_practical', 'official_theory_placeholder', 'official_practical_placeholder'] as $code) {
            $type = ExamType::query()->where('code', $code)->firstOrFail();

            $this->assertTrue($type->is_active);
            $this->assertSame(['ru', 'en', 'lt', 'pl'], array_keys($type->name_translations));
        }

        foreach (['draft', 'scheduled', 'open', 'in_progress', 'completed', 'cancelled', 'archived'] as $code) {
            $status = ExamStatus::query()->where('code', $code)->firstOrFail();

            $this->assertTrue($status->is_active);
            $this->assertSame(['ru', 'en', 'lt', 'pl'], array_keys($status->name_translations));
        }

        foreach (['planned', 'allowed', 'blocked', 'in_progress', 'passed', 'failed', 'no_show', 'cancelled', 'archived'] as $code) {
            $this->assertDatabaseHas('exam_attempt_statuses', ['code' => $code]);
        }

        foreach (['pending', 'passed', 'failed', 'needs_retake', 'cancelled'] as $code) {
            $this->assertDatabaseHas('exam_result_statuses', ['code' => $code]);
        }

        $this->assertSame(4, ExamAdmissionRule::query()->active()->count());
        $this->assertSame('Official theory placeholder', tkey('exams.types.official_theory_placeholder'));
        $this->assertSame('Scheduled', tkey('exams.session_statuses.scheduled'));
    }

    /**
     * @return array<string, int>
     */
    private function examSeedCounts(): array
    {
        return [
            'exam_types' => ExamType::query()->count(),
            'exam_statuses' => ExamStatus::query()->count(),
            'exam_attempt_statuses' => ExamAttemptStatus::query()->count(),
            'exam_result_statuses' => ExamResultStatus::query()->count(),
            'exam_admission_rules' => ExamAdmissionRule::query()->count(),
            'exam_translations' => TranslationString::query()->where('key', 'like', 'exams.%')->count(),
        ];
    }
}
