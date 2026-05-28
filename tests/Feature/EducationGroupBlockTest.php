<?php

namespace Tests\Feature;

use App\Actions\AddStudentToTrainingGroupAction;
use App\Actions\CreateOrUpdateTrainingGroupAction;
use App\Actions\RemoveStudentFromTrainingGroupAction;
use App\Enums\GroupStatus;
use App\Http\Requests\Education\LearningTopicRequest;
use App\Http\Requests\Education\TrainingGroupMembershipRequest;
use App\Http\Requests\Education\TrainingGroupSchedulePatternRequest;
use App\Http\Requests\Education\TrainingGroupStatusRequest;
use App\Http\Requests\TrainingGroupRequest;
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
use App\Rules\ActiveTrainingGroupStatusRule;
use App\Rules\TrainingGroupCanAcceptEnrollmentRule;
use App\Rules\TrainingGroupEnrollmentMatchesProgramRule;
use App\Rules\TrainingGroupMembershipNotDuplicateRule;
use App\Rules\ValidLearningTopicTypeRule;
use App\Rules\ValidScheduleDayRule;
use App\Rules\ValidSchedulePatternTimeRule;
use App\Rules\ValidTrainingGroupStatusRule;
use App\Support\Access\SuperadminPermissions;
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
            'training_programs',
            'course_modules',
            'learning_topics',
            'student_profiles',
            'enrollments',
            'marketing_leads',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertFalse(Schema::hasTable('learning_programs'));
        $this->assertFalse(Schema::hasTable('students'));
        $this->assertFalse(Schema::hasTable('student_enrollments'));
        $this->assertFalse(Schema::hasTable('leads'));

        foreach (['status_id', 'enrollment_closes_on', 'learning_notes', 'schedule_notes'] as $column) {
            $this->assertTrue(Schema::hasColumn('training_groups', $column), $column);
        }

        foreach (['uuid', 'code', 'title_translations', 'description_translations'] as $column) {
            $this->assertTrue(Schema::hasColumn('course_modules', $column), $column);
        }
    }

    public function test_group_models_relationships_scopes_and_helpers_work(): void
    {
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $program = LearningProgram::factory()->create(['title' => 'Block 4 Program']);
        $module = LearningProgramModule::factory()->theory()->create([
            'training_program_id' => $program->id,
            'title' => 'Theory module',
            'title_translations' => ['en' => 'Theory module', 'ru' => 'Theory module'],
        ]);
        $topic = LearningTopic::factory()->practice()->create([
            'training_program_id' => $program->id,
            'course_module_id' => $module->id,
            'title_translations' => ['en' => 'Practice topic', 'ru' => 'Practice topic'],
        ]);
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 8,
            'places_taken' => 2,
        ]);
        $pattern = TrainingGroupSchedulePattern::factory()->theory()->create([
            'training_group_id' => $group->id,
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => Student::factory()->active()->create()->id,
            'training_program_id' => $program->id,
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

        $group = $group->fresh(['statusRecord', 'memberships', 'schedulePatterns', 'activities']);

        $this->assertTrue($group->statusRecord->is($status));
        $this->assertTrue($group->acceptsEnrollment());
        $this->assertSame(6, $group->available_places);
        $this->assertCount(1, $group->memberships);
        $this->assertTrue($group->activeMemberships()->whereKey($membership->id)->exists());
        $this->assertTrue($group->schedulePatterns->first()->is($pattern));
        $this->assertTrue($group->activities()->where('type', 'student_added')->exists());
        $this->assertTrue($program->topics()->whereKey($topic->id)->exists());
        $this->assertTrue($module->topics()->whereKey($topic->id)->exists());
        $this->assertSame('Theory module', $module->displayTitle('en'));
        $this->assertSame('Practice topic', $topic->displayTitle('en'));
        $this->assertSame('Student added', $group->activities()->firstOrFail()->display_type);
    }

    public function test_status_seeder_and_translation_seeder_are_idempotent(): void
    {
        $this->seed(TrainingGroupStatusSeeder::class);
        $statusCount = TrainingGroupStatus::query()->count();

        $this->seed(TrainingGroupStatusSeeder::class);

        $this->assertSame($statusCount, TrainingGroupStatus::query()->count());
        $this->assertSame(1, TrainingGroupStatus::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('training_group_statuses', [
            'code' => 'planned',
            'is_default' => true,
            'accepts_enrollments' => true,
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

        $enrollment = app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $group, $user);

        $this->assertSame($group->id, $enrollment->training_group_id);
        $this->assertSame(1, $group->refresh()->places_taken);
        $this->assertDatabaseHas('training_group_memberships', [
            'training_group_id' => $group->id,
            'student_profile_id' => $student->id,
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
        $this->assertNull($enrollment->refresh()->training_group_id);
        $this->assertSame('left', $membership->refresh()->status);
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
            \Database\Factories\TrainingGroupStatusFactory::class,
            \Database\Factories\TrainingGroupMembershipFactory::class,
            \Database\Factories\LearningProgramFactory::class,
            \Database\Factories\LearningProgramModuleFactory::class,
            \Database\Factories\LearningTopicFactory::class,
            \Database\Factories\TrainingGroupSchedulePatternFactory::class,
            \Database\Factories\TrainingGroupActivityFactory::class,
            \Database\Seeders\TrainingGroupStatusSeeder::class,
            \Database\Seeders\EducationTranslationSeeder::class,
            \Database\Seeders\EducationGroupSeeder::class,
            \Database\Seeders\EducationSeeder::class,
            \App\Orchid\Screens\School\GroupListScreen::class,
            \App\Orchid\Screens\School\GroupEditScreen::class,
            \App\Orchid\Screens\School\TrainingGroupStatusListScreen::class,
            \App\Orchid\Screens\School\LearningTopicListScreen::class,
            \App\Orchid\Screens\School\TrainingGroupSchedulePatternListScreen::class,
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
