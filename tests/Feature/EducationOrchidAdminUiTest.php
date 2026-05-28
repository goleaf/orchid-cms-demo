<?php

namespace Tests\Feature;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\LearningProgram;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Models\User;
use Database\Seeders\EducationTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\TrainingGroupStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EducationOrchidAdminUiTest extends TestCase
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

    public function test_group_list_requires_view_permission_and_shows_group(): void
    {
        $group = TrainingGroup::factory()->create([
            'group_number' => 'EDU-UI-GROUP',
            'code' => 'EDU-UI-GROUP',
            'name_translations' => ['en' => 'UI education group', 'ru' => 'UI education group'],
        ]);

        $this->actingAs($this->userWithPermissions(['education.groups.view']))
            ->get(route('platform.education.groups'))
            ->assertOk()
            ->assertSee('EDU-UI-GROUP')
            ->assertSee('UI education group');

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.education.groups'))
            ->assertForbidden();

        $this->assertTrue($group->exists);
    }

    public function test_group_edit_requires_update_permission(): void
    {
        $group = TrainingGroup::factory()->create();

        $this->actingAs($this->userWithPermissions(['education.groups.view']))
            ->get(route('platform.education.groups.edit', $group))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.groups.update']))
            ->get(route('platform.education.groups.edit', $group))
            ->assertOk();
    }

    public function test_add_student_modal_action_requires_manage_students_permission(): void
    {
        [$group, $enrollment] = $this->groupAndEnrollmentForMembership();

        $payload = [
            'membership' => [
                'training_group_id' => $group->id,
                'enrollment_id' => $enrollment->id,
                'allow_overbooking' => false,
            ],
        ];

        $this->actingAs($this->userWithPermissions(['education.groups.update']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'addStudent']), $payload)
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.groups.update', 'education.groups.manage_students']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'addStudent']), $payload)
            ->assertRedirect(route('platform.education.groups.edit', $group->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_group_memberships', [
            'training_group_id' => $group->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'active',
        ]);
    }

    public function test_status_change_requires_change_status_permission_and_calls_action(): void
    {
        $draft = TrainingGroupStatus::query()->where('code', 'draft')->firstOrFail();
        $recruiting = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $group = TrainingGroup::factory()->create([
            'status' => GroupStatus::Planned,
            'status_id' => $draft->id,
        ]);

        $payload = [
            'group_id' => $group->id,
            'status_id' => $recruiting->id,
            'comment' => 'Ready for recruitment',
        ];

        $this->actingAs($this->userWithPermissions(['education.groups.update']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'changeStatus']), $payload)
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.groups.update', 'education.groups.change_status']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'changeStatus']), $payload)
            ->assertRedirect(route('platform.education.groups.edit', $group->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_groups', [
            'id' => $group->id,
            'status_id' => $recruiting->id,
        ]);
        $this->assertDatabaseHas('training_group_activities', [
            'training_group_id' => $group->id,
            'type' => 'status_changed',
        ]);
    }

    public function test_publish_on_site_requires_public_visibility_permission(): void
    {
        $group = $this->publishableGroup();

        $this->actingAs($this->userWithPermissions(['education.groups.update']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'publishOnSite']), [
                'group_id' => $group->id,
            ])
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.groups.update', 'education.groups.manage_public_visibility']))
            ->post(route('platform.education.groups.edit', ['group' => $group, 'method' => 'publishOnSite']), [
                'group_id' => $group->id,
            ])
            ->assertRedirect(route('platform.education.groups.edit', $group->id))
            ->assertSessionHasNoErrors();

        $this->assertTrue($group->fresh()->is_visible_on_site);
    }

    public function test_program_and_status_dictionary_screens_require_permissions(): void
    {
        $program = LearningProgram::factory()->create([
            'code' => 'EDU-UI-PROGRAM',
            'name_translations' => ['en' => 'UI learning program', 'ru' => 'UI learning program'],
        ]);

        foreach ([
            'platform.education.programs',
            'platform.education.programs.create',
            'platform.education.programs.edit',
            'platform.education.group-statuses',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), $routeName);
        }

        $this->actingAs($this->userWithPermissions(['education.programs.view']))
            ->get(route('platform.education.programs'))
            ->assertOk()
            ->assertSee('UI learning program');

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.education.programs'))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['education.groups.manage_statuses']))
            ->get(route('platform.education.group-statuses'))
            ->assertOk()
            ->assertSee(tkey('education.statuses.title'));

        $this->assertTrue($program->exists);
    }

    /**
     * @return array{0: TrainingGroup, 1: StudentEnrollment}
     */
    private function groupAndEnrollmentForMembership(): array
    {
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $program = TrainingProgram::factory()->create();
        $student = Student::factory()->active()->create();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'capacity' => 4,
            'capacity_total' => 4,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
            'training_group_id' => null,
        ]);

        return [$group, $enrollment];
    }

    private function publishableGroup(): TrainingGroup
    {
        $status = TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail();
        $program = TrainingProgram::factory()->create();
        $branch = Branch::factory()->create();

        return TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'status' => GroupStatus::Recruiting,
            'status_id' => $status->id,
            'name' => 'Publishable group',
            'name_translations' => ['en' => 'Publishable group'],
            'public_description_translations' => ['en' => 'Public group description'],
            'capacity' => 4,
            'capacity_total' => 4,
            'places_taken' => 0,
            'capacity_taken' => 0,
            'is_visible_on_site' => false,
        ]);
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
