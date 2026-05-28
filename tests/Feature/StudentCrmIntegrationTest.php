<?php

namespace Tests\Feature;

use App\Actions\ConvertLeadToStudentAction;
use App\Enums\GroupStatus;
use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\TrainingGroup;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\StudentTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentCrmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(StudentTranslationSeeder::class);
    }

    public function test_website_lead_converts_to_student_and_enrollment_with_source_context(): void
    {
        Notification::fake();

        $manager = User::factory()->create(['email' => 'admin@example.com']);
        $branch = Branch::factory()->active()->visibleOnSite()->create();
        $course = Course::factory()->active()->visibleOnSite()->create();
        $group = TrainingGroup::factory()->publicVisible()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'capacity' => 6,
            'places_taken' => 1,
        ]);

        $this->post(route('website.leads.store'), [
            'course_id' => $course->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'full_name' => 'Website Convert Lead',
            'phone' => '+370 600 55111',
            'email' => 'website-convert@example.test',
            'preferred_format' => 'group',
            'preferred_language' => 'en',
            'preferred_time' => 'evening',
            'preferred_messenger' => 'Telegram',
            'comment' => 'Convert me from public form.',
            'consent_accepted' => '1',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'student-conversion',
            'landing_page' => 'https://example.test/courses?utm_source=google',
            'form_page' => 'https://example.test/courses/category-b',
            'form_name' => 'student_conversion_application',
            'locale' => 'en',
        ])
            ->assertRedirect(route('website.thank_you'))
            ->assertSessionHasNoErrors();

        $lead = MarketingLead::query()
            ->where('email', 'website-convert@example.test')
            ->firstOrFail();
        $lead->forceFill(['status' => LeadStatus::Contacted])->save();

        $result = app(ConvertLeadToStudentAction::class)->handle(
            $lead->refresh(),
            null,
            [],
            [],
            true,
            true,
            true,
            $manager,
        );

        $student = $result['student']->refresh();
        $enrollment = $result['enrollment']->refresh();
        $lead = $lead->refresh();

        $this->assertSame($lead->id, $student->source_lead_id);
        $this->assertSame('website', $student->source_label);
        $this->assertTrue($student->consent_accepted);
        $this->assertNotNull($student->consent_accepted_at);
        $this->assertSame('en', $student->locale);
        $this->assertSame('Telegram', $student->preferred_messenger);
        $this->assertSame($manager->id, $student->manager_id);
        $this->assertSame($course->id, $enrollment->training_program_id);
        $this->assertSame($branch->id, $enrollment->branch_id);
        $this->assertSame($group->id, $enrollment->training_group_id);
        $this->assertSame(1, $group->refresh()->places_taken);
        $this->assertSame($student->id, $lead->converted_student_profile_id);
        $this->assertSame($enrollment->id, $lead->converted_enrollment_id);
        $this->assertNotNull($lead->converted_at);
        $this->assertNotNull($lead->closed_at);
        $this->assertSame(LeadStatus::Enrolled, $lead->status);
        $this->assertTrue($lead->activities()->where('type', 'converted')->exists());
        $this->assertTrue($student->activities()->where('type', 'created_from_lead')->exists());

        if (Schema::hasTable('training_group_memberships')) {
            $this->assertDatabaseHas('training_group_memberships', [
                'training_group_id' => $group->id,
                'student_profile_id' => $student->id,
            ]);
        } else {
            $this->assertDatabaseHas('enrollments', [
                'id' => $enrollment->id,
                'training_group_id' => $group->id,
            ]);
        }
    }

    public function test_crm_lead_converts_to_student_and_transfers_course_branch_and_group(): void
    {
        $manager = User::factory()->create();
        $branch = Branch::factory()->active()->visibleOnSite()->create();
        $course = Course::factory()->active()->visibleOnSite()->create();
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'status' => GroupStatus::Recruiting,
            'capacity' => 8,
            'places_taken' => 0,
        ]);
        $lead = MarketingLead::factory()->create([
            'responsible_manager_id' => $manager->id,
            'status' => LeadStatus::ReadyToEnroll,
            'source' => 'phone',
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'full_name' => 'CRM Convert Lead',
            'phone' => '+370 600 55222',
            'consent_accepted' => false,
        ]);

        $result = app(ConvertLeadToStudentAction::class)->handle($lead, null, [], [], false, false, false, $manager);

        $this->assertSame($lead->id, $result['student']->source_lead_id);
        $this->assertSame($course->id, $result['enrollment']->training_program_id);
        $this->assertSame($branch->id, $result['enrollment']->branch_id);
        $this->assertSame($group->id, $result['enrollment']->training_group_id);
        $this->assertSame(1, $group->refresh()->places_taken);
        $this->assertSame($result['student']->id, $lead->refresh()->converted_student_profile_id);
    }

    public function test_student_source_block_and_marketing_fields_respect_permissions_and_link_to_source_lead(): void
    {
        $manager = User::factory()->create(['name' => 'Source Manager']);
        $lead = MarketingLead::factory()->create([
            'lead_number' => 'LEAD-SOURCE-LINK',
            'responsible_manager_id' => $manager->id,
            'source' => 'website',
            'utm_source' => 'private-student-utm',
            'utm_campaign' => 'private-student-campaign',
            'landing_page' => 'https://example.test/landing-source',
            'form_page' => 'https://example.test/form-source',
            'form_name' => 'source_link_form',
        ]);
        $student = Student::factory()->active()->create([
            'source_lead_id' => $lead->id,
        ]);

        $this->actingAs($this->userWithPermissions(['students.update', 'students.view_crm_source']))
            ->get(route('platform.students.edit', $student))
            ->assertOk()
            ->assertSee('LEAD-SOURCE-LINK')
            ->assertSee('Source Manager')
            ->assertSee(route('platform.crm.leads.edit', $lead), false)
            ->assertDontSee('private-student-utm')
            ->assertDontSee('https://example.test/landing-source');

        $this->actingAs($this->userWithPermissions(['students.update', 'students.view_crm_source', 'crm.leads.view_marketing']))
            ->get(route('platform.students.edit', $student))
            ->assertOk()
            ->assertSee('private-student-utm')
            ->assertSee('https://example.test/landing-source')
            ->assertSee('https://example.test/form-source');
    }

    public function test_converted_lead_cannot_be_converted_again(): void
    {
        $manager = User::factory()->create();
        $course = Course::factory()->active()->visibleOnSite()->create();
        $lead = MarketingLead::factory()->create([
            'status' => LeadStatus::Contacted,
            'source' => 'phone',
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'phone' => '+370 600 55333',
        ]);

        app(ConvertLeadToStudentAction::class)->handle($lead, null, [], [], false, false, false, $manager);

        $this->expectException(ValidationException::class);
        app(ConvertLeadToStudentAction::class)->handle($lead->refresh(), null, [], [], false, false, false, $manager);
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
