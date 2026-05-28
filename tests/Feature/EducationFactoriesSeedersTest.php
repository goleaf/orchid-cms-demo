<?php

namespace Tests\Feature;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupActivity;
use App\Models\TrainingGroupMembership;
use App\Models\TrainingGroupSchedulePattern;
use App\Models\TrainingGroupStatus;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use Database\Seeders\DemoTrainingGroupMembershipSeeder;
use Database\Seeders\DemoTrainingGroupSeeder;
use Database\Seeders\EducationTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\LearningProgramModuleSeeder;
use Database\Seeders\LearningProgramSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\TrainingGroupStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationFactoriesSeedersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        $this->seed(LanguageSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(TrainingGroupStatusSeeder::class);
    }

    public function test_education_factories_create_valid_records(): void
    {
        $program = LearningProgram::factory()->translated()->create();
        $module = LearningProgramModule::factory()->practice()->translated()->create([
            'learning_program_id' => $program->id,
        ]);
        $topic = LearningTopic::factory()->parking()->create([
            'training_program_id' => $program->course_id,
            'learning_program_module_id' => $module->id,
        ]);
        $group = TrainingGroup::factory()
            ->recruiting()
            ->translated()
            ->withCapacity(16, 3)
            ->create([
                'training_program_id' => $program->course_id,
                'course_id' => $program->course_id,
                'course_category_id' => $program->course_category_id,
                'learning_program_id' => $program->id,
            ]);
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->course_id,
            'course_category_id' => $program->course_category_id,
            'training_group_id' => $group->id,
        ]);
        $membership = TrainingGroupMembership::factory()->active()->create([
            'training_group_id' => $group->id,
            'student_profile_id' => $student->id,
            'enrollment_id' => $enrollment->id,
        ]);
        $pattern = TrainingGroupSchedulePattern::factory()
            ->mondayEvening()
            ->translated()
            ->create(['training_group_id' => $group->id]);
        $activity = TrainingGroupActivity::factory()->studentAdded()->create([
            'training_group_id' => $group->id,
            'student_profile_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'membership_id' => $membership->id,
        ]);

        $this->assertDatabaseHas('training_group_statuses', ['code' => 'recruiting']);
        $this->assertTrue($group->statusRecord->is(TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail()));
        $this->assertTrue($group->learningProgram->is($program));
        $this->assertTrue($module->program->is($program));
        $this->assertTrue($topic->module->is($module));
        $this->assertSame(13, $group->available_places);
        $this->assertTrue($membership->group->is($group));
        $this->assertTrue($membership->student->is($student));
        $this->assertTrue($membership->enrollment->is($enrollment));
        $this->assertSame('18:00-20:00', $pattern->display_time_range);
        $this->assertSame('student_added', $activity->type);
    }

    public function test_training_group_factory_capacity_and_visibility_states_work(): void
    {
        $custom = TrainingGroup::factory()->withCapacity(20, 5)->visibleOnSite()->acceptingApplications()->create();
        $full = TrainingGroup::factory()->fullCapacity()->create();
        $almostFull = TrainingGroup::factory()->almostFullCapacity()->create();
        $empty = TrainingGroup::factory()->emptyCapacity()->hiddenFromSite()->create();

        $this->assertSame(20, $custom->capacity_total);
        $this->assertSame(5, $custom->capacity_taken);
        $this->assertTrue($custom->is_visible_on_site);
        $this->assertTrue($custom->is_accepting_applications);
        $this->assertTrue($full->is_full);
        $this->assertTrue($almostFull->is_almost_full);
        $this->assertSame(12, $empty->available_places);
        $this->assertFalse($empty->is_visible_on_site);
        $this->assertFalse($empty->is_accepting_applications);
    }

    public function test_required_block_four_factory_states_exist(): void
    {
        $this->assertFactoryStates(TrainingGroupStatus::factory(), [
            'draft',
            'recruiting',
            'almostFull',
            'full',
            'closed',
            'scheduled',
            'active',
            'paused',
            'completed',
            'cancelled',
            'archived',
            'default',
            'activeStatus',
            'inactive',
            'public',
            'openForEnrollment',
            'final',
            'success',
            'cancelledStatus',
            'archivedStatus',
            'translated',
        ]);
        $this->assertFactoryStates(TrainingGroup::factory(), [
            'draft',
            'recruiting',
            'almostFull',
            'full',
            'closed',
            'scheduled',
            'active',
            'paused',
            'completed',
            'cancelled',
            'archived',
            'visibleOnSite',
            'hiddenFromSite',
            'acceptingApplications',
            'notAcceptingApplications',
            'featured',
            'fullCapacity',
            'almostFullCapacity',
            'emptyCapacity',
            'startingSoon',
            'started',
            'ended',
            'evening',
            'morning',
            'weekend',
            'withCourse',
            'withCourseCategory',
            'withBranch',
            'withLearningProgram',
            'withManager',
            'withAdministrator',
            'withTeacher',
            'translated',
        ]);
        $this->assertFactoryStates(TrainingGroupMembership::factory(), [
            'invited',
            'pending',
            'active',
            'waitlisted',
            'transferred',
            'removed',
            'completed',
            'cancelled',
            'withStudent',
            'withEnrollment',
            'withGroup',
            'joinedToday',
            'leftToday',
        ]);
        $this->assertFactoryStates(LearningProgram::factory(), [
            'active',
            'inactive',
            'default',
            'translated',
            'forCategoryB',
            'forCategoryA',
            'forIndividualLessons',
            'forExamPreparation',
            'standard',
            'intensive',
        ]);
        $this->assertFactoryStates(LearningProgramModule::factory(), [
            'theory',
            'practice',
            'examPreparation',
            'internalExam',
            'stateExamPreparation',
            'documents',
            'onboarding',
            'other',
            'required',
            'optional',
            'active',
            'inactive',
            'translated',
        ]);
        $this->assertFactoryStates(LearningTopic::factory(), [
            'required',
            'optional',
            'active',
            'inactive',
            'translated',
            'trafficRules',
            'roadSigns',
            'parking',
            'cityDriving',
            'highwayDriving',
            'examRoute',
            'firstDrive',
            'safety',
        ]);
        $this->assertFactoryStates(TrainingGroupSchedulePattern::factory(), [
            'theory',
            'practice',
            'consultation',
            'examPreparation',
            'mondayEvening',
            'wednesdayEvening',
            'weekendMorning',
            'weekdayMorning',
            'active',
            'inactive',
            'translated',
        ]);
        $this->assertFactoryStates(TrainingGroupActivity::factory(), [
            'created',
            'updated',
            'archived',
            'statusChanged',
            'studentAdded',
            'studentRemoved',
            'studentWaitlisted',
            'studentTransferredIn',
            'studentTransferredOut',
            'membershipCompleted',
            'schedulePatternCreated',
            'schedulePatternUpdated',
            'schedulePatternDeleted',
            'capacityChanged',
            'publishedOnSite',
            'hiddenFromSite',
            'completed',
            'cancelled',
            'noteAdded',
            'learningProgramAssigned',
            'teacherAssigned',
            'managerAssigned',
        ]);
    }

    public function test_training_group_status_and_translation_seeders_are_idempotent(): void
    {
        $this->seed(TrainingGroupStatusSeeder::class);
        $statusCount = TrainingGroupStatus::query()->count();

        $this->seed(TrainingGroupStatusSeeder::class);

        $this->assertSame($statusCount, TrainingGroupStatus::query()->count());
        $this->assertSame(1, TrainingGroupStatus::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('training_group_statuses', [
            'code' => 'draft',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('training_group_statuses', [
            'code' => 'recruiting',
            'is_open_for_enrollment' => true,
        ]);

        $this->seed(EducationTranslationSeeder::class);
        $translation = TranslationString::query()
            ->where('key', 'education.groups.title')
            ->firstOrFail();

        $this->seed(EducationTranslationSeeder::class);

        $this->assertDatabaseHas('translation_strings', ['key' => 'education.groups.title']);
        $this->assertSame('Training groups', tkey('education.groups.title', [], 'en'));
        $this->assertEqualsCanonicalizing(
            ['en', 'lt', 'pl', 'ru'],
            TranslationValue::query()
                ->where('translation_string_id', $translation->id)
                ->pluck('language_code')
                ->all(),
        );
    }

    public function test_learning_program_seeders_create_programs_modules_and_topics_idempotently(): void
    {
        $this->seed(LearningProgramModuleSeeder::class);

        $programCount = LearningProgram::query()->count();
        $moduleCount = LearningProgramModule::query()->count();
        $topicCount = LearningTopic::query()->count();

        $this->seed(LearningProgramSeeder::class);
        $this->seed(LearningProgramModuleSeeder::class);

        $this->assertSame($programCount, LearningProgram::query()->count());
        $this->assertSame($moduleCount, LearningProgramModule::query()->count());
        $this->assertSame($topicCount, LearningTopic::query()->count());
        $this->assertDatabaseHas('learning_programs', [
            'code' => 'category_b_standard',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('learning_programs', ['code' => 'category_b_intensive']);
        $this->assertDatabaseHas('learning_programs', ['code' => 'individual_lessons']);
        $this->assertDatabaseHas('learning_programs', ['code' => 'exam_preparation']);
        $this->assertDatabaseHas('learning_programs', ['code' => 'skill_recovery']);
        $this->assertDatabaseHas('learning_program_modules', ['code' => 'category_b_theory']);
        $this->assertDatabaseHas('learning_topics', ['code' => 'category_b_traffic_rules']);
    }

    public function test_demo_training_group_seeders_create_demo_data_idempotently(): void
    {
        $this->seed(DemoTrainingGroupSeeder::class);

        $groupCount = TrainingGroup::query()->where('code', 'like', 'DEMO-%')->count();
        $patternCount = TrainingGroupSchedulePattern::query()->count();

        $this->seed(DemoTrainingGroupSeeder::class);

        $this->assertSame(5, $groupCount);
        $this->assertSame($groupCount, TrainingGroup::query()->where('code', 'like', 'DEMO-%')->count());
        $this->assertSame($patternCount, TrainingGroupSchedulePattern::query()->count());
        $this->assertDatabaseHas('training_groups', ['code' => 'DEMO-B-EVENING']);
        $this->assertDatabaseHas('training_groups', ['code' => 'DEMO-B-WEEKEND']);
        $this->assertDatabaseHas('training_groups', ['code' => 'DEMO-B-INTENSIVE']);
        $this->assertDatabaseHas('training_groups', ['code' => 'DEMO-EXAM-PREP']);
        $this->assertDatabaseHas('training_groups', ['code' => 'DEMO-INDIVIDUAL']);

        $this->seed(DemoTrainingGroupMembershipSeeder::class);

        $membershipCount = TrainingGroupMembership::query()->count();
        $studentCount = Student::query()->where('email', 'like', 'education-demo-student-%')->count();

        $this->seed(DemoTrainingGroupMembershipSeeder::class);

        $this->assertSame(5, $membershipCount);
        $this->assertSame(5, $studentCount);
        $this->assertSame($membershipCount, TrainingGroupMembership::query()->count());
        $this->assertSame($studentCount, Student::query()->where('email', 'like', 'education-demo-student-%')->count());
    }

    /**
     * @param  array<int, string>  $states
     */
    private function assertFactoryStates(object $factory, array $states): void
    {
        foreach ($states as $state) {
            $this->assertTrue(method_exists($factory, $state), $factory::class.'::'.$state);
        }
    }
}
