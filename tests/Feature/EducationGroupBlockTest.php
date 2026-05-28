<?php

namespace Tests\Feature;

use App\Actions\AddStudentToTrainingGroupAction;
use App\Actions\AddTrainingGroupNoteAction;
use App\Actions\ArchiveTrainingGroupAction;
use App\Actions\AssignLearningProgramToGroupAction;
use App\Actions\ChangeTrainingGroupStatusAction;
use App\Actions\CompleteTrainingGroupMembershipAction;
use App\Actions\CreateLearningProgramAction;
use App\Actions\CreateLearningProgramModuleAction;
use App\Actions\CreateLearningTopicAction;
use App\Actions\CreateOrUpdateTrainingGroupAction;
use App\Actions\CreateTrainingGroupAction;
use App\Actions\CreateTrainingGroupSchedulePatternAction;
use App\Actions\DeleteTrainingGroupSchedulePatternAction;
use App\Actions\GenerateTrainingGroupNumberAction;
use App\Actions\HideTrainingGroupFromSiteAction;
use App\Actions\PublishTrainingGroupOnSiteAction;
use App\Actions\RecalculateTrainingGroupCapacityAction;
use App\Actions\RemoveStudentFromTrainingGroupAction;
use App\Actions\TransferStudentBetweenGroupsAction;
use App\Actions\UpdateLearningProgramAction;
use App\Actions\UpdateLearningProgramModuleAction;
use App\Actions\UpdateLearningTopicAction;
use App\Actions\UpdateTrainingGroupAction;
use App\Actions\UpdateTrainingGroupSchedulePatternAction;
use App\Actions\WaitlistStudentForTrainingGroupAction;
use App\Enums\GroupStatus;
use App\Http\Requests\Education\AddStudentToTrainingGroupRequest;
use App\Http\Requests\Education\AddTrainingGroupNoteRequest;
use App\Http\Requests\Education\ArchiveTrainingGroupRequest;
use App\Http\Requests\Education\AssignLearningProgramToGroupRequest;
use App\Http\Requests\Education\ChangeTrainingGroupStatusRequest;
use App\Http\Requests\Education\CompleteTrainingGroupMembershipRequest;
use App\Http\Requests\Education\DeleteTrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\HideTrainingGroupRequest;
use App\Http\Requests\Education\LearningTopicRequest;
use App\Http\Requests\Education\PublishTrainingGroupRequest;
use App\Http\Requests\Education\RemoveStudentFromTrainingGroupRequest;
use App\Http\Requests\Education\StoreLearningProgramModuleRequest;
use App\Http\Requests\Education\StoreLearningProgramRequest;
use App\Http\Requests\Education\StoreLearningTopicRequest;
use App\Http\Requests\Education\StoreTrainingGroupRequest;
use App\Http\Requests\Education\StoreTrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\TrainingGroupMembershipRequest;
use App\Http\Requests\Education\TrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\TrainingGroupStatusRequest;
use App\Http\Requests\Education\TransferStudentBetweenGroupsRequest;
use App\Http\Requests\Education\UpdateLearningProgramModuleRequest;
use App\Http\Requests\Education\UpdateLearningProgramRequest;
use App\Http\Requests\Education\UpdateLearningTopicRequest;
use App\Http\Requests\Education\UpdateTrainingGroupRequest;
use App\Http\Requests\Education\UpdateTrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\WaitlistStudentForTrainingGroupRequest;
use App\Http\Requests\TrainingGroupRequest;
use App\Models\Branch;
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
use App\Models\TrainingProgram;
use App\Models\TranslationString;
use App\Models\User;
use App\Orchid\Screens\School\GroupEditScreen;
use App\Orchid\Screens\School\GroupListScreen;
use App\Orchid\Screens\School\LearningTopicListScreen;
use App\Orchid\Screens\School\TrainingGroupSchedulePatternListScreen;
use App\Orchid\Screens\School\TrainingGroupStatusListScreen;
use App\Rules\ActiveTrainingGroupStatusRule;
use App\Rules\DuplicateSchedulePatternRule;
use App\Rules\GroupCanBePublishedRule;
use App\Rules\GroupMembershipCanBeRemovedRule;
use App\Rules\GroupMembershipCanBeTransferredRule;
use App\Rules\LearningProgramIsActiveRule;
use App\Rules\SchedulePatternTimeRangeRule;
use App\Rules\StudentEnrollmentCanJoinGroupRule;
use App\Rules\StudentEnrollmentNotAlreadyInActiveGroupRule;
use App\Rules\TrainingGroupCanAcceptApplicationsRule;
use App\Rules\TrainingGroupCanAcceptEnrollmentRule;
use App\Rules\TrainingGroupCanBeArchivedRule;
use App\Rules\TrainingGroupCanBeUpdatedRule;
use App\Rules\TrainingGroupCapacityRule;
use App\Rules\TrainingGroupDateRangeRule;
use App\Rules\TrainingGroupEnrollmentMatchesProgramRule;
use App\Rules\TrainingGroupMembershipNotDuplicateRule;
use App\Rules\TrainingGroupOpenForEnrollmentRule;
use App\Rules\TranslatedGroupNameRequiredRule;
use App\Rules\TranslatedLearningProgramNameRequiredRule;
use App\Rules\ValidDayOfWeekRule;
use App\Rules\ValidLearningProgramModuleTypeRule;
use App\Rules\ValidLearningTopicTypeRule;
use App\Rules\ValidScheduleDayRule;
use App\Rules\ValidSchedulePatternTimeRule;
use App\Rules\ValidSchedulePatternTypeRule;
use App\Rules\ValidTrainingGroupCapacityValueRule;
use App\Rules\ValidTrainingGroupStatusRule;
use App\Rules\ValidTrainingGroupStatusTransitionRule;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\LearningProgramFactory;
use Database\Factories\LearningProgramModuleFactory;
use Database\Factories\LearningTopicFactory;
use Database\Factories\TrainingGroupActivityFactory;
use Database\Factories\TrainingGroupMembershipFactory;
use Database\Factories\TrainingGroupSchedulePatternFactory;
use Database\Factories\TrainingGroupStatusFactory;
use Database\Seeders\EducationGroupSeeder;
use Database\Seeders\EducationSeeder;
use Database\Seeders\EducationTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\TrainingGroupStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EducationGroupBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(EducationTranslationSeeder::class);
        $this->seed(TrainingGroupStatusSeeder::class);
    }

    public function test_block_four_database_foundation_reuses_existing_core_tables(): void
    {
        foreach ([
            'training_groups',
            'training_group_statuses',
            'training_group_memberships',
            'training_group_schedule_patterns',
            'training_group_activities',
            'learning_programs',
            'learning_program_modules',
            'training_programs',
            'course_modules',
            'learning_topics',
            'student_profiles',
            'enrollments',
            'marketing_leads',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertFalse(Schema::hasTable('students'));
        $this->assertFalse(Schema::hasTable('student_enrollments'));
        $this->assertFalse(Schema::hasTable('leads'));

        foreach ([
            'uuid',
            'group_number',
            'code',
            'course_id',
            'training_program_id',
            'course_category_id',
            'branch_id',
            'status_id',
            'learning_program_id',
            'manager_id',
            'administrator_id',
            'teacher_id',
            'name_translations',
            'description_translations',
            'public_description_translations',
            'schedule_summary_translations',
            'start_date',
            'planned_end_date',
            'actual_end_date',
            'capacity_total',
            'capacity_reserved',
            'capacity_taken',
            'capacity_waitlist',
            'is_visible_on_site',
            'is_featured',
            'is_accepting_applications',
            'timezone',
            'default_lesson_duration_minutes',
            'notes',
            'internal_notes',
            'enrollment_closes_on',
            'learning_notes',
            'schedule_notes',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('training_groups', $column), $column);
        }

        foreach (['code', 'name_translations', 'is_open_for_enrollment', 'is_archived'] as $column) {
            $this->assertTrue(Schema::hasColumn('training_group_statuses', $column), $column);
        }

        foreach (['student_id', 'student_enrollment_id', 'transfer_from_group_id', 'transfer_to_group_id', 'transfer_reason'] as $column) {
            $this->assertTrue(Schema::hasColumn('training_group_memberships', $column), $column);
        }

        foreach (['uuid', 'course_id', 'course_category_id', 'code', 'name_translations', 'description_translations'] as $column) {
            $this->assertTrue(Schema::hasColumn('learning_programs', $column), $column);
        }

        foreach (['learning_program_id', 'code', 'type', 'name_translations', 'required_hours'] as $column) {
            $this->assertTrue(Schema::hasColumn('learning_program_modules', $column), $column);
        }

        foreach (['learning_program_module_id', 'name_translations', 'estimated_hours'] as $column) {
            $this->assertTrue(Schema::hasColumn('learning_topics', $column), $column);
        }

        foreach (['type', 'day_of_week', 'start_time', 'end_time', 'classroom_id', 'location_translations', 'notes_translations'] as $column) {
            $this->assertTrue(Schema::hasColumn('training_group_schedule_patterns', $column), $column);
        }

        foreach (['student_id', 'student_enrollment_id', 'membership_id', 'type', 'meta'] as $column) {
            $this->assertTrue(Schema::hasColumn('training_group_activities', $column), $column);
        }
    }

    public function test_group_models_relationships_scopes_and_helpers_work(): void
    {
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $course = TrainingProgram::factory()->create(['title' => 'Block 4 Course']);
        $program = LearningProgram::factory()->create([
            'course_id' => $course->id,
            'name_translations' => ['en' => 'Block 4 Program', 'ru' => 'Block 4 Program'],
        ]);
        $module = LearningProgramModule::factory()->theory()->create([
            'learning_program_id' => $program->id,
            'name_translations' => ['en' => 'Theory module', 'ru' => 'Theory module'],
            'required_hours' => 12,
        ]);
        $topic = LearningTopic::factory()->practice()->create([
            'training_program_id' => $course->id,
            'learning_program_module_id' => $module->id,
            'name_translations' => ['en' => 'Practice topic', 'ru' => 'Practice topic'],
        ]);
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $course->id,
            'course_id' => $course->id,
            'learning_program_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 8,
            'capacity_total' => 8,
            'capacity_taken' => 2,
            'places_taken' => 2,
        ]);
        $pattern = TrainingGroupSchedulePattern::factory()->theory()->create([
            'training_group_id' => $group->id,
            'day_of_week' => 1,
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $course->id,
        ]);
        $membership = TrainingGroupMembership::factory()->active()->create([
            'training_group_id' => $group->id,
            'student_profile_id' => $enrollment->student_profile_id,
            'enrollment_id' => $enrollment->id,
        ]);
        TrainingGroupActivity::factory()->studentAdded()->create([
            'training_group_id' => $group->id,
            'membership_id' => $membership->id,
            'enrollment_id' => $enrollment->id,
            'student_profile_id' => $enrollment->student_profile_id,
        ]);

        $group = $group->fresh(['statusRecord', 'learningProgram.modules.topics', 'memberships', 'students', 'schedulePatterns', 'activities']);

        $this->assertTrue($group->statusRecord->is($status));
        $this->assertTrue($group->learningProgram->is($program));
        $this->assertTrue($group->acceptsEnrollment());
        $this->assertSame(6, $group->available_places);
        $this->assertSame(25, $group->capacity_percent);
        $this->assertFalse($group->is_full);
        $this->assertCount(1, $group->memberships);
        $this->assertTrue($group->activeMemberships()->whereKey($membership->id)->exists());
        $this->assertTrue($group->students->first()->is($student));
        $this->assertTrue($student->trainingGroups()->whereKey($group->id)->exists());
        $this->assertTrue($enrollment->groupMemberships()->whereKey($membership->id)->exists());
        $this->assertTrue($enrollment->activeGroupMembership()->whereKey($membership->id)->exists());
        $this->assertTrue($group->schedulePatterns->first()->is($pattern));
        $this->assertTrue($group->activities()->where('type', 'student_added')->exists());
        $this->assertTrue($program->topics()->whereKey($topic->id)->exists());
        $this->assertTrue($module->topics()->whereKey($topic->id)->exists());
        $this->assertSame('Theory module', $module->displayTitle('en'));
        $this->assertSame('Practice topic', $topic->displayTitle('en'));
        $this->assertSame(12.0, $program->fresh('modules')->total_required_hours);
        $this->assertSame('Monday', $pattern->display_day);
        $this->assertSame('18:00-20:00', $pattern->display_time_range);
        $this->assertSame('Student added', $group->activities()->firstOrFail()->display_type);

        $this->assertTrue(TrainingGroup::query()->search('GROUP')->whereKey($group->id)->exists());
        $this->assertTrue(TrainingGroup::query()->visibleOnSite()->whereKey($group->id)->exists());
        $this->assertTrue(TrainingGroup::query()->openForEnrollment()->whereKey($group->id)->exists());
    }

    public function test_status_seeder_and_translation_seeder_are_idempotent(): void
    {
        $this->seed(TrainingGroupStatusSeeder::class);
        $statusCount = TrainingGroupStatus::query()->count();

        $this->seed(TrainingGroupStatusSeeder::class);

        $this->assertSame($statusCount, TrainingGroupStatus::query()->count());
        $this->assertSame(1, TrainingGroupStatus::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('training_group_statuses', [
            'code' => 'draft',
            'is_default' => true,
            'accepts_enrollments' => false,
        ]);
        $this->assertDatabaseHas('training_group_statuses', [
            'code' => 'recruiting',
            'is_open_for_enrollment' => true,
        ]);

        $this->seed(EducationTranslationSeeder::class);
        $translationCount = TranslationString::query()->where('key', 'menu.education')->count();

        $this->seed(EducationTranslationSeeder::class);

        $this->assertSame($translationCount, TranslationString::query()->where('key', 'menu.education')->count());
        $this->assertSame('Training groups', tkey('menu.education.groups'));
        $this->assertSame('The group cannot accept this enrollment.', tkey('education.validation.group_cannot_accept_enrollment'));
        $this->assertSame('Invalid group status transition.', tkey('education.groups.validation.invalid_status_transition'));
        $this->assertSame('Group capacity changed', tkey('education.activities.titles.capacity_changed'));
    }

    public function test_group_membership_action_updates_capacity_membership_and_activity(): void
    {
        $user = User::factory()->create();
        $program = TrainingProgram::factory()->create();
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
            'training_group_id' => null,
        ]);
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 2,
            'places_taken' => 0,
        ]);

        $membership = app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $group, $user);
        $enrollment = $enrollment->refresh();

        $this->assertSame($group->id, $enrollment->training_group_id);
        $this->assertSame($enrollment->id, $membership->enrollment_id);
        $this->assertSame(1, $group->refresh()->places_taken);
        $this->assertSame(1, $group->capacity_taken);
        $this->assertDatabaseHas('training_group_memberships', [
            'training_group_id' => $group->id,
            'student_id' => $student->id,
            'student_profile_id' => $student->id,
            'student_enrollment_id' => $enrollment->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'student_added',
        ]);

        app(AddStudentToTrainingGroupAction::class)->handle($enrollment->refresh(), $group, $user);

        $this->assertSame(1, $group->refresh()->places_taken);
        $this->assertSame(1, TrainingGroupMembership::query()->where('training_group_id', $group->id)->where('enrollment_id', $enrollment->id)->count());

        $membership = TrainingGroupMembership::query()->where('enrollment_id', $enrollment->id)->firstOrFail();
        app(RemoveStudentFromTrainingGroupAction::class)->handle($membership, $user, 'cancelled');

        $this->assertSame(0, $group->refresh()->places_taken);
        $this->assertSame(0, $group->capacity_taken);
        $this->assertNull($enrollment->refresh()->training_group_id);
        $this->assertSame('removed', $membership->refresh()->status);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'student_removed',
        ]);
    }

    public function test_training_group_actions_manage_status_schedule_publication_and_learning_programs(): void
    {
        $user = User::factory()->create();
        $manager = User::factory()->create();
        $teacher = User::factory()->create();
        $course = TrainingProgram::factory()->create();
        $branch = Branch::factory()->create();
        $draft = TrainingGroupStatus::query()->where('code', 'draft')->firstOrFail();
        $recruiting = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();

        TrainingGroup::factory()->create([
            'group_number' => 'GRP-'.now()->year.'-0001',
        ]);

        $number = app(GenerateTrainingGroupNumberAction::class)->handle();
        $this->assertSame('GRP-'.now()->year.'-0002', $number);

        $group = app(CreateTrainingGroupAction::class)->handle([
            'group_number' => $number,
            'training_program_id' => $course->id,
            'course_id' => $course->id,
            'branch_id' => $branch->id,
            'status_id' => $draft->id,
            'name_translations' => ['en' => 'Action group'],
            'capacity_total' => 2,
            'capacity' => 2,
        ], $user);

        $this->assertSame($number, $group->group_number);
        $this->assertSame($draft->id, $group->status_id);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'created',
        ]);

        $group = app(ChangeTrainingGroupStatusAction::class)->handle($group, $recruiting, $user);

        $this->assertSame($recruiting->id, $group->status_id);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'status_changed',
        ]);

        $learningProgram = app(CreateLearningProgramAction::class)->handle([
            'course_id' => $course->id,
            'code' => 'category_b_actions',
            'name_translations' => ['en' => 'Category B action program'],
            'is_default' => true,
            'is_active' => true,
        ], $user);
        $learningProgram = app(UpdateLearningProgramAction::class)->handle($learningProgram, [
            'course_id' => $course->id,
            'code' => 'category_b_actions',
            'name_translations' => ['en' => 'Updated Category B action program'],
            'is_default' => true,
            'is_active' => true,
        ], $user);
        $module = app(CreateLearningProgramModuleAction::class)->handle([
            'learning_program_id' => $learningProgram->id,
            'code' => 'theory_actions',
            'type' => 'theory',
            'name_translations' => ['en' => 'Theory actions'],
            'required_hours' => 12,
            'is_required' => true,
            'is_active' => true,
        ], $user);
        $module = app(UpdateLearningProgramModuleAction::class)->handle($module, [
            'learning_program_id' => $learningProgram->id,
            'code' => 'theory_actions',
            'type' => 'theory',
            'name_translations' => ['en' => 'Updated theory actions'],
            'required_hours' => 14,
            'is_required' => true,
            'is_active' => true,
        ], $user);
        $topic = app(CreateLearningTopicAction::class)->handle([
            'learning_program_module_id' => $module->id,
            'code' => 'traffic_rules_actions',
            'name_translations' => ['en' => 'Traffic rules actions'],
            'topic_type' => 'theory',
            'estimated_hours' => 2,
            'is_required' => true,
            'is_active' => true,
        ], $user);
        $topic = app(UpdateLearningTopicAction::class)->handle($topic, [
            'learning_program_module_id' => $module->id,
            'code' => 'traffic_rules_actions',
            'name_translations' => ['en' => 'Updated traffic rules actions'],
            'topic_type' => 'theory',
            'estimated_hours' => 3,
            'is_required' => true,
            'is_active' => true,
        ], $user);

        $this->assertSame('Updated Category B action program', $learningProgram->display_name);
        $this->assertSame('Updated theory actions', $module->display_name);
        $this->assertSame('Updated traffic rules actions', $topic->display_name);

        $group = app(AssignLearningProgramToGroupAction::class)->handle($group, $learningProgram, $user);
        $group = app(UpdateTrainingGroupAction::class)->handle($group, [
            'capacity_total' => 3,
            'capacity' => 3,
            'learning_program_id' => $learningProgram->id,
            'teacher_id' => $teacher->id,
            'manager_id' => $manager->id,
        ], $user);

        $this->assertSame($learningProgram->id, $group->learning_program_id);
        $this->assertSame($teacher->id, $group->teacher_id);
        $this->assertSame($manager->id, $group->manager_id);
        $this->assertDatabaseHas('training_group_activities', ['training_group_id' => $group->id, 'type' => 'learning_program_assigned']);
        $this->assertDatabaseHas('training_group_activities', ['training_group_id' => $group->id, 'type' => 'teacher_assigned']);
        $this->assertDatabaseHas('training_group_activities', ['training_group_id' => $group->id, 'type' => 'manager_assigned']);

        $pattern = app(CreateTrainingGroupSchedulePatternAction::class)->handle([
            'training_group_id' => $group->id,
            'type' => 'theory',
            'day_of_week' => 1,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'is_active' => true,
        ], $user);
        $pattern = app(UpdateTrainingGroupSchedulePatternAction::class)->handle($pattern, [
            'day_of_week' => 3,
            'start_time' => '18:30',
            'end_time' => '20:30',
        ], $user);

        $this->assertSame(3, $pattern->day_of_week);
        app(DeleteTrainingGroupSchedulePatternAction::class)->handle($pattern, $user);
        $this->assertFalse($pattern->refresh()->is_active);

        $group = app(PublishTrainingGroupOnSiteAction::class)->handle($group->refresh(), $user);
        $this->assertTrue($group->is_visible_on_site);
        $this->assertTrue($group->is_accepting_applications);

        $group = app(HideTrainingGroupFromSiteAction::class)->handle($group, $user);
        $this->assertFalse($group->is_visible_on_site);
        $this->assertFalse($group->is_accepting_applications);

        app(AddTrainingGroupNoteAction::class)->handle($group, 'Internal group note', $user);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'note_added',
            'body' => 'Internal group note',
        ]);
    }

    public function test_group_membership_actions_prevent_overbooking_waitlist_transfer_and_complete(): void
    {
        $user = User::factory()->create();
        $program = TrainingProgram::factory()->create();
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $source = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 1,
            'capacity_total' => 1,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $target = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 2,
            'capacity_total' => 2,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'training_program_id' => $program->id,
            'training_group_id' => null,
        ]);
        $secondEnrollment = StudentEnrollment::factory()->active()->create([
            'training_program_id' => $program->id,
            'training_group_id' => null,
        ]);

        $membership = app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $source, $user);
        $source = app(RecalculateTrainingGroupCapacityAction::class)->handle($source, $user);

        $this->assertSame(1, $source->capacity_taken);
        $this->assertTrue($source->is_full);

        $this->expectException(ValidationException::class);
        app(AddStudentToTrainingGroupAction::class)->handle($secondEnrollment, $source, $user);
    }

    public function test_waitlist_transfer_complete_and_archive_actions_work(): void
    {
        $user = User::factory()->create();
        $program = TrainingProgram::factory()->create();
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $source = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 1,
            'capacity_total' => 1,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $target = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 2,
            'capacity_total' => 2,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create(['training_program_id' => $program->id, 'training_group_id' => null]);
        $waitlistedEnrollment = StudentEnrollment::factory()->active()->create(['training_program_id' => $program->id, 'training_group_id' => null]);

        $membership = app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $source, $user);
        $waitlisted = app(WaitlistStudentForTrainingGroupAction::class)->handle($waitlistedEnrollment, $source->refresh(), $user);

        $this->assertSame('waitlisted', $waitlisted->status);
        $this->assertSame(1, $source->refresh()->capacity_waitlist);

        $newMembership = app(TransferStudentBetweenGroupsAction::class)->handle($membership->refresh(), $target, $user, false, 'schedule change');

        $this->assertSame('transferred', $membership->refresh()->status);
        $this->assertSame('active', $newMembership->status);
        $this->assertSame($target->id, $enrollment->refresh()->training_group_id);
        $this->assertSame(0, $source->refresh()->capacity_taken);
        $this->assertSame(1, $target->refresh()->capacity_taken);

        app(CompleteTrainingGroupMembershipAction::class)->handle($newMembership, $user);
        $this->assertSame('completed', $newMembership->refresh()->status);
        $this->assertNull($enrollment->refresh()->training_group_id);

        $activeEnrollment = StudentEnrollment::factory()->active()->create(['training_program_id' => $program->id, 'training_group_id' => null]);
        app(AddStudentToTrainingGroupAction::class)->handle($activeEnrollment, $target->refresh(), $user);

        $this->expectException(ValidationException::class);
        app(ArchiveTrainingGroupAction::class)->handle($target->refresh(), $user);
    }

    public function test_group_capacity_and_program_rules_return_translated_errors(): void
    {
        $program = TrainingProgram::factory()->create();
        $otherProgram = TrainingProgram::factory()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'training_program_id' => $program->id,
        ]);
        $closedStatus = TrainingGroupStatus::query()->where('code', 'closed')->firstOrFail();
        $fullGroup = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $closedStatus->id,
            'capacity' => 1,
            'places_taken' => 1,
        ]);
        $mismatchGroup = TrainingGroup::factory()->create([
            'training_program_id' => $otherProgram->id,
            'capacity' => 10,
            'places_taken' => 0,
        ]);

        $validator = Validator::make(
            ['training_group_id' => $fullGroup->id],
            ['training_group_id' => [new TrainingGroupCanAcceptEnrollmentRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.group_cannot_accept_enrollment'), $validator->errors()->first('training_group_id'));

        $validator = Validator::make(
            ['training_group_id' => $mismatchGroup->id],
            ['training_group_id' => [new TrainingGroupEnrollmentMatchesProgramRule($enrollment)]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.enrollment_program_mismatch'), $validator->errors()->first('training_group_id'));

        $this->expectException(ValidationException::class);
        app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $fullGroup);
    }

    public function test_custom_rules_validate_topics_statuses_schedule_and_duplicate_membership(): void
    {
        $status = TrainingGroupStatus::query()->where('code', 'closed')->firstOrFail();
        $program = TrainingProgram::factory()->create();
        $enrollment = StudentEnrollment::factory()->active()->create(['training_program_id' => $program->id]);
        $group = TrainingGroup::factory()->create(['training_program_id' => $program->id]);
        TrainingGroupMembership::factory()->create([
            'training_group_id' => $group->id,
            'student_profile_id' => $enrollment->student_profile_id,
            'enrollment_id' => $enrollment->id,
        ]);

        $validator = Validator::make(['status_id' => $status->id], ['status_id' => [new ActiveTrainingGroupStatusRule]]);
        $this->assertFalse($validator->fails());

        $status->forceFill(['is_active' => false])->save();
        $validator = Validator::make(['status_id' => $status->id], ['status_id' => [new ActiveTrainingGroupStatusRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.status_not_active'), $validator->errors()->first('status_id'));

        $validator = Validator::make(['status' => 'bad-status'], ['status' => [new ValidTrainingGroupStatusRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.invalid_group_status'), $validator->errors()->first('status'));

        $validator = Validator::make(['topic_type' => 'bad-type'], ['topic_type' => [new ValidLearningTopicTypeRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.invalid_learning_topic_type'), $validator->errors()->first('topic_type'));

        $validator = Validator::make(['day' => 8], ['day' => [new ValidScheduleDayRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.invalid_schedule_day'), $validator->errors()->first('day'));

        $validator = Validator::make(
            ['pattern' => ['starts_at' => '18:00', 'ends_at' => '17:00']],
            ['pattern.ends_at' => [new ValidSchedulePatternTimeRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.schedule_end_after_start'), $validator->errors()->first('pattern.ends_at'));

        $validator = Validator::make(
            ['membership' => ['training_group_id' => $group->id, 'enrollment_id' => $enrollment->id]],
            ['membership.enrollment_id' => [new TrainingGroupMembershipNotDuplicateRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.validation.duplicate_membership'), $validator->errors()->first('membership.enrollment_id'));
    }

    public function test_block_four_custom_rules_return_translated_errors(): void
    {
        $program = TrainingProgram::factory()->create();
        $draft = TrainingGroupStatus::query()->where('code', 'draft')->firstOrFail();
        $active = TrainingGroupStatus::query()->where('code', 'active')->firstOrFail();
        $closed = TrainingGroupStatus::query()->where('code', 'closed')->firstOrFail();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'status_id' => $draft->id,
            'status' => GroupStatus::Planned,
            'capacity' => 1,
            'capacity_total' => 1,
            'places_taken' => 1,
            'capacity_taken' => 1,
        ]);
        $closedGroup = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'status_id' => $closed->id,
            'status' => GroupStatus::Closed,
            'capacity' => 2,
            'capacity_total' => 2,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create(['training_program_id' => $program->id]);
        $membership = TrainingGroupMembership::factory()->completed()->create([
            'training_group_id' => $group->id,
            'student_profile_id' => $enrollment->student_profile_id,
            'enrollment_id' => $enrollment->id,
        ]);
        TrainingGroupSchedulePattern::factory()->theory()->create([
            'training_group_id' => $group->id,
            'day_of_week' => 1,
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);
        $inactiveProgram = LearningProgram::factory()->inactive()->create();

        $validator = Validator::make(['status_id' => $active->id], ['status_id' => [new ValidTrainingGroupStatusTransitionRule($group)]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.invalid_status_transition'), $validator->errors()->first('status_id'));

        $validator = Validator::make(['capacity' => 0], ['capacity' => [new ValidTrainingGroupCapacityValueRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.invalid_capacity'), $validator->errors()->first('capacity'));

        $validator = Validator::make(['group_id' => $group->id], ['group_id' => [new TrainingGroupCapacityRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.capacity_exceeded'), $validator->errors()->first('group_id'));

        $validator = Validator::make(['group_id' => $closedGroup->id], ['group_id' => [new TrainingGroupOpenForEnrollmentRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.group_not_open_for_enrollment'), $validator->errors()->first('group_id'));

        $validator = Validator::make(
            ['group' => ['start_date' => now()->addDay()->toDateString(), 'planned_end_date' => now()->toDateString()]],
            ['group.planned_end_date' => [new TrainingGroupDateRangeRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.start_date_after_end_date'), $validator->errors()->first('group.planned_end_date'));

        $validator = Validator::make(['day' => 8], ['day' => [new ValidDayOfWeekRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.invalid_day_of_week'), $validator->errors()->first('day'));

        $validator = Validator::make(
            ['pattern' => ['start_time' => '20:00', 'end_time' => '18:00']],
            ['pattern.end_time' => [new SchedulePatternTimeRangeRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.end_time_before_start_time'), $validator->errors()->first('pattern.end_time'));

        $validator = Validator::make(
            ['pattern' => ['training_group_id' => $group->id, 'day_of_week' => 1, 'type' => 'theory', 'start_time' => '18:00', 'end_time' => '20:00']],
            ['pattern.end_time' => [new DuplicateSchedulePatternRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.duplicate_schedule_pattern'), $validator->errors()->first('pattern.end_time'));

        $validator = Validator::make(['type' => 'bad'], ['type' => [new ValidSchedulePatternTypeRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.invalid_schedule_pattern_type'), $validator->errors()->first('type'));

        $validator = Validator::make(['type' => 'bad'], ['type' => [new ValidLearningProgramModuleTypeRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.invalid_module_type'), $validator->errors()->first('type'));

        $validator = Validator::make(['program_id' => $inactiveProgram->id], ['program_id' => [new LearningProgramIsActiveRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.learning_program_not_active'), $validator->errors()->first('program_id'));

        $validator = Validator::make(['name_translations' => ['en' => '']], ['name_translations' => [new TranslatedGroupNameRequiredRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.default_group_name_required'), $validator->errors()->first('name_translations'));

        $validator = Validator::make(['name_translations' => ['en' => '']], ['name_translations' => [new TranslatedLearningProgramNameRequiredRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.default_learning_program_name_required'), $validator->errors()->first('name_translations'));

        $validator = Validator::make(['group' => $closedGroup->id], ['group' => [new GroupCanBePublishedRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.group_cannot_be_published'), $validator->errors()->first('group'));

        $validator = Validator::make(['membership_id' => $membership->id], ['membership_id' => [new GroupMembershipCanBeTransferredRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.membership_cannot_be_transferred'), $validator->errors()->first('membership_id'));

        $validator = Validator::make(['membership_id' => $membership->id], ['membership_id' => [new GroupMembershipCanBeRemovedRule]]);
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('education.groups.validation.membership_cannot_be_removed'), $validator->errors()->first('membership_id'));
    }

    public function test_training_group_screen_save_and_member_modal_use_actions_and_requests(): void
    {
        $this->seed();

        $actor = $this->userWithPermissions([
            'education.groups.create',
            'education.groups.update',
            'education.manage_memberships',
        ]);
        $program = TrainingProgram::factory()->create();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'capacity' => 4,
            'places_taken' => 0,
        ]);
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
            'training_group_id' => null,
        ]);

        $this->actingAs($actor)
            ->post(route('platform.website.groups.edit', ['group' => $group, 'method' => 'save']), [
                'group' => [
                    'id' => $group->id,
                    'branch_id' => $group->branch_id,
                    'training_program_id' => $program->id,
                    'instructor_id' => $group->instructor_id,
                    'status' => GroupStatus::Recruiting->value,
                    'code' => 'BLOCK4-GROUP',
                    'capacity' => 4,
                    'places_taken' => 0,
                    'starts_on' => now()->addWeek()->toDateString(),
                    'meeting_days' => 'monday,wednesday',
                    'meeting_time' => '18:00',
                    'end_time' => '20:00',
                    'is_visible_on_site' => true,
                ],
                'name_translations' => ['ru' => 'Block 4 Group', 'en' => 'Block 4 Group'],
            ])
            ->assertRedirect(route('platform.website.groups'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_groups', [
            'id' => $group->id,
            'code' => 'BLOCK4-GROUP',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.website.groups.edit', ['group' => $group, 'method' => 'addMember']), [
                'membership' => [
                    'training_group_id' => $group->id,
                    'enrollment_id' => $enrollment->id,
                    'allow_overbooking' => false,
                ],
            ])
            ->assertRedirect(route('platform.website.groups.edit', $group->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_group_memberships', [
            'training_group_id' => $group->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'active',
        ]);
    }

    public function test_education_orchid_routes_and_permissions_work(): void
    {
        $this->seed();

        foreach ([
            'platform.education.groups',
            'platform.education.groups.create',
            'platform.education.groups.edit',
            'platform.education.group-statuses',
            'platform.education.learning-topics',
            'platform.education.schedule-patterns',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), $routeName);
        }

        $group = TrainingGroup::factory()->create([
            'code' => 'EDU-ROUTE-GROUP',
            'starts_on' => now()->subDay()->toDateString(),
            'start_date' => now()->subDay()->toDateString(),
        ]);
        LearningTopic::factory()->create(['code' => 'EDU-TOPIC']);
        TrainingGroupSchedulePattern::factory()->create(['training_group_id' => $group->id]);

        $this->actingAs($this->userWithPermissions(['education.groups.view']))
            ->get(route('platform.education.groups'))
            ->assertOk()
            ->assertSee('EDU-ROUTE-GROUP');

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.education.groups'))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.manage_statuses']))
            ->get(route('platform.education.group-statuses'))
            ->assertOk()
            ->assertSee(tkey('education.statuses.title'));

        $this->actingAs($this->userWithPermissions(['education.manage_topics']))
            ->get(route('platform.education.learning-topics'))
            ->assertOk()
            ->assertSee('EDU-TOPIC');

        $this->actingAs($this->userWithPermissions(['education.manage_schedule_patterns']))
            ->get(route('platform.education.schedule-patterns'))
            ->assertOk()
            ->assertSee(tkey('education.schedule_patterns.title'));
    }

    public function test_block_four_artifacts_permissions_and_request_classes_exist(): void
    {
        foreach ([
            TrainingGroupStatus::class,
            TrainingGroupMembership::class,
            LearningProgram::class,
            LearningProgramModule::class,
            LearningTopic::class,
            TrainingGroupSchedulePattern::class,
            TrainingGroupActivity::class,
            GenerateTrainingGroupNumberAction::class,
            CreateTrainingGroupAction::class,
            UpdateTrainingGroupAction::class,
            ChangeTrainingGroupStatusAction::class,
            ArchiveTrainingGroupAction::class,
            RecalculateTrainingGroupCapacityAction::class,
            CreateOrUpdateTrainingGroupAction::class,
            AddStudentToTrainingGroupAction::class,
            RemoveStudentFromTrainingGroupAction::class,
            WaitlistStudentForTrainingGroupAction::class,
            TransferStudentBetweenGroupsAction::class,
            CompleteTrainingGroupMembershipAction::class,
            CreateTrainingGroupSchedulePatternAction::class,
            UpdateTrainingGroupSchedulePatternAction::class,
            DeleteTrainingGroupSchedulePatternAction::class,
            PublishTrainingGroupOnSiteAction::class,
            HideTrainingGroupFromSiteAction::class,
            AssignLearningProgramToGroupAction::class,
            CreateLearningProgramAction::class,
            UpdateLearningProgramAction::class,
            CreateLearningProgramModuleAction::class,
            UpdateLearningProgramModuleAction::class,
            CreateLearningTopicAction::class,
            UpdateLearningTopicAction::class,
            AddTrainingGroupNoteAction::class,
            TrainingGroupRequest::class,
            StoreTrainingGroupRequest::class,
            UpdateTrainingGroupRequest::class,
            ChangeTrainingGroupStatusRequest::class,
            ArchiveTrainingGroupRequest::class,
            AddStudentToTrainingGroupRequest::class,
            RemoveStudentFromTrainingGroupRequest::class,
            WaitlistStudentForTrainingGroupRequest::class,
            TransferStudentBetweenGroupsRequest::class,
            CompleteTrainingGroupMembershipRequest::class,
            StoreTrainingGroupSchedulePatternRequest::class,
            UpdateTrainingGroupSchedulePatternRequest::class,
            DeleteTrainingGroupSchedulePatternRequest::class,
            PublishTrainingGroupRequest::class,
            HideTrainingGroupRequest::class,
            AssignLearningProgramToGroupRequest::class,
            StoreLearningProgramRequest::class,
            UpdateLearningProgramRequest::class,
            StoreLearningProgramModuleRequest::class,
            UpdateLearningProgramModuleRequest::class,
            StoreLearningTopicRequest::class,
            UpdateLearningTopicRequest::class,
            AddTrainingGroupNoteRequest::class,
            TrainingGroupStatusRequest::class,
            TrainingGroupMembershipRequest::class,
            LearningTopicRequest::class,
            TrainingGroupSchedulePatternRequest::class,
            TrainingGroupCanAcceptEnrollmentRule::class,
            ValidTrainingGroupStatusTransitionRule::class,
            TrainingGroupCanBeUpdatedRule::class,
            TrainingGroupCanBeArchivedRule::class,
            TrainingGroupCapacityRule::class,
            ValidTrainingGroupCapacityValueRule::class,
            StudentEnrollmentCanJoinGroupRule::class,
            StudentEnrollmentNotAlreadyInActiveGroupRule::class,
            TrainingGroupOpenForEnrollmentRule::class,
            TrainingGroupCanAcceptApplicationsRule::class,
            TrainingGroupDateRangeRule::class,
            ValidDayOfWeekRule::class,
            SchedulePatternTimeRangeRule::class,
            DuplicateSchedulePatternRule::class,
            ValidSchedulePatternTypeRule::class,
            LearningProgramIsActiveRule::class,
            ValidLearningProgramModuleTypeRule::class,
            TranslatedGroupNameRequiredRule::class,
            TranslatedLearningProgramNameRequiredRule::class,
            GroupCanBePublishedRule::class,
            GroupMembershipCanBeTransferredRule::class,
            GroupMembershipCanBeRemovedRule::class,
            TrainingGroupEnrollmentMatchesProgramRule::class,
            TrainingGroupMembershipNotDuplicateRule::class,
            ActiveTrainingGroupStatusRule::class,
            ValidTrainingGroupStatusRule::class,
            ValidLearningTopicTypeRule::class,
            ValidScheduleDayRule::class,
            ValidSchedulePatternTimeRule::class,
            TrainingGroupStatusFactory::class,
            TrainingGroupMembershipFactory::class,
            LearningProgramFactory::class,
            LearningProgramModuleFactory::class,
            LearningTopicFactory::class,
            TrainingGroupSchedulePatternFactory::class,
            TrainingGroupActivityFactory::class,
            TrainingGroupStatusSeeder::class,
            EducationTranslationSeeder::class,
            EducationGroupSeeder::class,
            EducationSeeder::class,
            GroupListScreen::class,
            GroupEditScreen::class,
            TrainingGroupStatusListScreen::class,
            LearningTopicListScreen::class,
            TrainingGroupSchedulePatternListScreen::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }

        foreach ([
            'education.groups.view',
            'education.groups.create',
            'education.groups.update',
            'education.groups.override_status_transition',
            'education.manage_statuses',
            'education.manage_memberships',
            'education.manage_schedule_patterns',
            'education.manage_topics',
            'education.view_activities',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
            $this->assertNotSame('permissions.'.$permission, tkey('permissions.'.$permission));
        }
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ])->save();

        return $user;
    }
}
