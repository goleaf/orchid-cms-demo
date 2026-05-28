<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentOrchidAdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_students_view_can_open_student_list_and_see_student(): void
    {
        $this->seed();

        $student = Student::factory()->active()->create([
            'student_number' => 'STU-2026-9001',
            'first_name' => 'Admin',
            'last_name' => 'Student',
            'full_name' => 'Admin Student',
        ]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students'))
            ->assertOk()
            ->assertSee('STU-2026-9001')
            ->assertSee('Admin Student')
            ->assertSee(tkey('students.title'));

        $this->assertDatabaseHas('student_profiles', ['id' => $student->id]);
    }

    public function test_user_without_students_view_cannot_open_student_list(): void
    {
        $this->seed();

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.students'))
            ->assertForbidden();
    }

    public function test_student_edit_crm_source_and_marketing_sections_respect_permissions(): void
    {
        $this->seed();

        $lead = MarketingLead::factory()->create([
            'lead_number' => 'LEAD-2026-STUDENT',
            'utm_source' => 'private-student-source',
            'utm_campaign' => 'private-student-campaign',
        ]);
        $student = Student::factory()->active()->create([
            'source_lead_id' => $lead->id,
        ]);

        $this->actingAs($this->userWithPermissions(['students.update']))
            ->get(route('platform.students.edit', $student))
            ->assertOk()
            ->assertDontSee('LEAD-2026-STUDENT')
            ->assertDontSee('private-student-source');

        $this->actingAs($this->userWithPermissions(['students.update', 'students.view_crm_source']))
            ->get(route('platform.students.edit', $student))
            ->assertOk()
            ->assertSee('LEAD-2026-STUDENT')
            ->assertDontSee('private-student-source');

        $this->actingAs($this->userWithPermissions(['students.update', 'students.view_crm_source', 'students.view_marketing']))
            ->get(route('platform.students.edit', $student))
            ->assertOk()
            ->assertSee('LEAD-2026-STUDENT')
            ->assertSee('private-student-source');
    }

    public function test_enrollment_task_dictionary_and_conversion_screens_require_permissions(): void
    {
        $this->seed();

        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->waitingDocuments()->create([
            'student_profile_id' => $student->id,
        ]);
        $task = StudentTask::factory()->open()->create([
            'student_id' => $student->id,
            'title_translations' => ['ru' => 'Student permission task', 'en' => 'Student permission task'],
        ]);
        $lead = MarketingLead::factory()->create([
            'status' => LeadStatus::Contacted,
            'phone' => '+37060011111',
            'consent_accepted' => true,
        ]);

        $viewer = $this->userWithPermissions(['students.view']);

        $this->actingAs($viewer)
            ->get(route('platform.students.enrollments.edit', $enrollment))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('platform.students.tasks'))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('platform.students.statuses'))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('platform.crm.leads.convert', $lead))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['students.manage_enrollments']))
            ->get(route('platform.students.enrollments.edit', $enrollment))
            ->assertOk()
            ->assertSee($enrollment->enrollment_number);

        $this->actingAs($this->userWithPermissions(['students.manage_tasks']))
            ->get(route('platform.students.tasks'))
            ->assertOk()
            ->assertSee('Student permission task');

        $this->assertDatabaseHas('student_tasks', ['id' => $task->id]);

        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->get(route('platform.students.statuses'))
            ->assertOk()
            ->assertSee(tkey('menu.students.statuses'));

        $this->actingAs($this->userWithPermissions(['students.convert_from_lead']))
            ->get(route('platform.crm.leads.convert', $lead))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['crm.leads.convert']))
            ->get(route('platform.crm.leads.convert', $lead))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['students.convert_from_lead', 'crm.leads.convert']))
            ->get(route('platform.crm.leads.convert', $lead))
            ->assertOk()
            ->assertSee(tkey('students.conversion.title'));
    }

    public function test_student_screen_modal_methods_call_actions(): void
    {
        $this->seed();

        $actor = $this->userWithPermissions([
            'students.update',
            'students.manage_tasks',
            'students.manage_enrollments',
        ]);
        $manager = $this->userWithPermissions();
        $student = Student::factory()->active()->create();
        $program = TrainingProgram::factory()->create();

        $this->actingAs($actor)
            ->post(route('platform.students.edit', ['student' => $student, 'method' => 'assignManager']), [
                'manager_id' => $manager->id,
            ])
            ->assertRedirect(route('platform.students.edit', $student))
            ->assertSessionHasNoErrors();

        $this->assertSame($manager->id, $student->refresh()->manager_id);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'manager_assigned',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.students.edit', ['student' => $student, 'method' => 'addNote']), [
                'body' => 'Internal student note',
            ])
            ->assertRedirect(route('platform.students.edit', $student))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'note_added',
            'body' => 'Internal student note',
        ]);

        $this->actingAs($actor)
            ->post(route('platform.students.edit', ['student' => $student, 'method' => 'createTask']), [
                'task' => [
                    'title_translations' => ['ru' => 'Prepare student contract'],
                    'priority' => 'high',
                    'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
                ],
            ])
            ->assertRedirect(route('platform.students.edit', $student))
            ->assertSessionHasNoErrors();

        $task = $student->tasks()->where('priority', 'high')->firstOrFail();

        $this->actingAs($actor)
            ->post(route('platform.students.edit', ['student' => $student, 'method' => 'completeTask']), [
                'task' => $task->id,
            ])
            ->assertRedirect(route('platform.students.edit', $student))
            ->assertSessionHasNoErrors();

        $this->assertSame('done', $task->refresh()->status);

        $this->actingAs($actor)
            ->post(route('platform.students.edit', ['student' => $student, 'method' => 'addEnrollment']), [
                'enrollment' => [
                    'student_id' => $student->id,
                    'training_program_id' => $program->id,
                    'status' => 'waiting_documents',
                    'price' => 1200,
                    'payment_status' => 'pending',
                ],
            ])
            ->assertRedirect(route('platform.students.edit', $student))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollments', [
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
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
