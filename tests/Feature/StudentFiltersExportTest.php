<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentFiltersExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_search_by_name_works(): void
    {
        Student::factory()->active()->create($this->studentName('Searchable Driver'));
        Student::factory()->active()->create($this->studentName('Hidden Rider'));

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['search' => 'Searchable']))
            ->assertOk()
            ->assertSee('Searchable Driver')
            ->assertDontSee('Hidden Rider');
    }

    public function test_search_by_phone_works_with_normalized_phone(): void
    {
        Student::factory()->active()->create([
            ...$this->studentName('Phone Match'),
            'full_name' => 'Phone Match',
            'phone' => '+37060000000',
            'normalized_phone' => '+37060000000',
        ]);
        Student::factory()->active()->create([
            ...$this->studentName('Other Phone'),
            'phone' => '+37061111111',
            'normalized_phone' => '+37061111111',
        ]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['search' => '600 00000']))
            ->assertOk()
            ->assertSee('Phone Match')
            ->assertDontSee('Other Phone');
    }

    public function test_search_by_email_works(): void
    {
        Student::factory()->active()->create([
            ...$this->studentName('Email Match'),
            'email' => 'student-search@example.test',
        ]);
        Student::factory()->active()->create([
            ...$this->studentName('Email Hidden'),
            'email' => 'hidden-search@example.test',
        ]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['search' => 'student-search@example.test']))
            ->assertOk()
            ->assertSee('Email Match')
            ->assertDontSee('Email Hidden');
    }

    public function test_search_by_student_number_works(): void
    {
        Student::factory()->active()->create([
            'student_number' => 'STU-2026-FIND',
            ...$this->studentName('Number Match'),
        ]);
        Student::factory()->active()->create([
            'student_number' => 'STU-2026-HIDE',
            ...$this->studentName('Number Hidden'),
        ]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['search' => 'STU-2026-FIND']))
            ->assertOk()
            ->assertSee('Number Match')
            ->assertDontSee('Number Hidden');
    }

    public function test_active_segment_works(): void
    {
        Student::factory()->active()->create($this->studentName('Active Segment'));
        Student::factory()->archived()->create($this->studentName('Archived Segment'));

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['segment' => 'active']))
            ->assertOk()
            ->assertSee('Active Segment')
            ->assertDontSee('Archived Segment');
    }

    public function test_waiting_documents_segment_works(): void
    {
        $matching = Student::factory()->active()->create($this->studentName('Waiting Documents Segment'));
        $hidden = Student::factory()->active()->create($this->studentName('Payment Segment Hidden'));

        StudentEnrollment::factory()->waitingDocuments()->create(['student_profile_id' => $matching->id]);
        StudentEnrollment::factory()->waitingPayment()->create(['student_profile_id' => $hidden->id]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['segment' => 'waiting_documents']))
            ->assertOk()
            ->assertSee('Waiting Documents Segment')
            ->assertDontSee('Payment Segment Hidden');
    }

    public function test_waiting_payment_segment_works(): void
    {
        $matching = Student::factory()->active()->create($this->studentName('Waiting Payment Segment'));
        $hidden = Student::factory()->active()->create($this->studentName('Documents Segment Hidden'));

        StudentEnrollment::factory()->waitingPayment()->create(['student_profile_id' => $matching->id]);
        StudentEnrollment::factory()->waitingDocuments()->create(['student_profile_id' => $hidden->id]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['segment' => 'waiting_payment']))
            ->assertOk()
            ->assertSee('Waiting Payment Segment')
            ->assertDontSee('Documents Segment Hidden');
    }

    public function test_without_group_segment_works(): void
    {
        $matching = Student::factory()->active()->create($this->studentName('Without Group Segment'));
        $hidden = Student::factory()->active()->create($this->studentName('With Group Segment'));
        $group = TrainingGroup::factory()->create();

        StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $matching->id,
            'training_group_id' => null,
        ]);
        StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $hidden->id,
            'training_group_id' => $group->id,
        ]);

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['segment' => 'without_group']))
            ->assertOk()
            ->assertSee('Without Group Segment')
            ->assertDontSee('With Group Segment');
    }

    public function test_archived_segment_works(): void
    {
        Student::factory()->archived()->create($this->studentName('Archived Segment Match'));
        Student::factory()->active()->create($this->studentName('Active Segment Hidden'));

        $this->actingAs($this->userWithPermissions(['students.view']))
            ->get(route('platform.students', ['segment' => 'archived']))
            ->assertOk()
            ->assertSee('Archived Segment Match')
            ->assertDontSee('Active Segment Hidden');
    }

    public function test_export_requires_permission(): void
    {
        $this->actingAs($this->userWithPermissions(['students.view']))
            ->post(route('platform.students', ['method' => 'export']))
            ->assertForbidden();
    }

    public function test_export_works_with_permission(): void
    {
        Carbon::setTestNow('2026-05-28 10:00:00');

        $student = Student::factory()->active()->create([
            'student_number' => 'STU-2026-CSV',
            ...$this->studentName('Export Student'),
            'email' => 'student-export@example.test',
        ]);
        StudentEnrollment::factory()->waitingDocuments()->create([
            'student_profile_id' => $student->id,
            'enrollment_number' => 'ENR-2026-CSV',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['students.view', 'students.export']))
            ->post(route('platform.students', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('students-2026-05-28.csv', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('STU-2026-CSV', $content);
        $this->assertStringContainsString('Export Student', $content);
        $this->assertStringContainsString('student-export@example.test', $content);
        $this->assertStringContainsString('ENR-2026-CSV', $content);
    }

    public function test_export_hides_crm_source_and_marketing_fields_without_permission(): void
    {
        $lead = Lead::factory()->withUtm()->create([
            'source' => 'website',
            'utm_source' => 'student-secret-google',
            'utm_medium' => 'student-secret-cpc',
            'utm_campaign' => 'student-secret-campaign',
            'landing_page' => 'https://drive.test/?secret=student',
            'form_page' => 'https://drive.test/apply',
        ]);
        Student::factory()->active()->create([
            'student_number' => 'STU-2026-NO-MARKETING',
            'source_lead_id' => $lead->id,
            'source_label' => 'student-secret-source',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['students.view', 'students.export']))
            ->post(route('platform.students', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('STU-2026-NO-MARKETING', $content);
        $this->assertStringNotContainsString(tkey('students.fields.source_lead'), $content);
        $this->assertStringNotContainsString('student-secret-source', $content);
        $this->assertStringNotContainsString(tkey('crm.leads.fields.utm_source'), $content);
        $this->assertStringNotContainsString('student-secret-google', $content);
        $this->assertStringNotContainsString('student-secret-campaign', $content);
        $this->assertStringNotContainsString('https://drive.test/?secret=student', $content);
    }

    public function test_export_includes_marketing_fields_with_permission(): void
    {
        $lead = Lead::factory()->withUtm()->create([
            'source' => 'website',
            'utm_source' => 'student-google-export',
            'utm_medium' => 'student-cpc-export',
            'utm_campaign' => 'student-campaign-export',
            'landing_page' => 'https://drive.test/?utm_source=student-google-export',
            'form_page' => 'https://drive.test/courses',
        ]);
        Student::factory()->active()->create([
            'student_number' => 'STU-2026-MARKETING',
            'source_lead_id' => $lead->id,
            'source_label' => 'website',
        ]);

        $response = $this->actingAs($this->userWithPermissions([
            'students.view',
            'students.export',
            'students.view_crm_source',
            'students.view_marketing',
        ]))
            ->post(route('platform.students', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString(tkey('students.fields.source_lead'), $content);
        $this->assertStringContainsString(tkey('crm.leads.fields.utm_source'), $content);
        $this->assertStringContainsString('STU-2026-MARKETING', $content);
        $this->assertStringContainsString('student-google-export', $content);
        $this->assertStringContainsString('student-campaign-export', $content);
        $this->assertStringContainsString('https://drive.test/?utm_source=student-google-export', $content);
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

    /**
     * @return array<string, string|null>
     */
    private function studentName(string $name): array
    {
        return [
            'full_name' => $name,
            'first_name' => $name,
            'middle_name' => null,
            'last_name' => '',
        ];
    }
}
