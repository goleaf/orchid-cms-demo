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
use App\Rules\TrainingGroupCanAcceptEnrollmentRule;
use App\Rules\TrainingGroupCanAcceptApplicationsRule;
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
use App\Rules\ValidSchedulePatternTypeRule;
use App\Rules\ValidSchedulePatternTimeRule;
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

        $group = TrainingGroup::factory()->create(['code' => 'EDU-ROUTE-GROUP']);
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
            CreateOrUpdateTrainingGroupAction::class,
            AddStudentToTrainingGroupAction::class,
            RemoveStudentFromTrainingGroupAction::class,
            TrainingGroupRequest::class,
            TrainingGroupStatusRequest::class,
            TrainingGroupMembershipRequest::class,
            LearningTopicRequest::class,
            TrainingGroupSchedulePatternRequest::class,
            TrainingGroupCanAcceptEnrollmentRule::class,
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
