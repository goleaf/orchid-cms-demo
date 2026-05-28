<?php

namespace Tests\Feature;

use App\Actions\DeleteEnrollmentStatusAction;
use App\Actions\DeleteStudentStatusAction;
use App\Models\EnrollmentStatus;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentStatus;
use App\Models\User;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\StudentTranslationSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentDictionaryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed([
            LanguageSeeder::class,
            SystemTranslationSeeder::class,
            StudentDictionarySeeder::class,
            StudentTranslationSeeder::class,
        ]);
    }

    public function test_superadmin_can_list_student_statuses(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('platform.students.statuses'))
            ->assertOk()
            ->assertSee(tkey('menu.students.statuses'))
            ->assertSee(StudentStatus::translatedLabel('active'));
    }

    public function test_user_without_permission_cannot_list_statuses(): void
    {
        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.students.statuses'))
            ->assertForbidden();
    }

    public function test_student_status_can_be_created(): void
    {
        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.statuses', ['method' => 'save']), [
                'status' => $this->studentStatusPayload('screening'),
            ])
            ->assertRedirect(route('platform.students.statuses'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('student_statuses', [
            'code' => 'screening',
            'is_active' => true,
            'is_system' => false,
        ]);
    }

    public function test_student_status_can_be_updated(): void
    {
        $status = StudentStatus::factory()->create([
            'code' => 'paperwork',
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.statuses', ['method' => 'save']), [
                'status' => $this->studentStatusPayload('paperwork', [
                    'id' => $status->id,
                    'color' => '#0f766e',
                    'sort_order' => 42,
                    'name_translations' => $this->translations('Paperwork'),
                ]),
            ])
            ->assertRedirect(route('platform.students.statuses'))
            ->assertSessionHasNoErrors();

        $status->refresh();

        $this->assertSame('#0f766e', $status->color);
        $this->assertSame(42, $status->sort_order);
        $this->assertSame('Paperwork', $status->getTranslation('name', 'en'));
    }

    public function test_only_one_default_student_status_exists(): void
    {
        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.statuses', ['method' => 'save']), [
                'status' => $this->studentStatusPayload('fresh_default', [
                    'is_default' => '1',
                ]),
            ])
            ->assertRedirect(route('platform.students.statuses'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, StudentStatus::query()->where('is_default', true)->count());
        $this->assertTrue(StudentStatus::query()->where('code', 'fresh_default')->firstOrFail()->is_default);
    }

    public function test_used_student_status_cannot_be_deleted(): void
    {
        $status = StudentStatus::factory()->create([
            'code' => 'used_student_status',
            'is_system' => false,
        ]);
        Student::factory()->create(['status_id' => $status->id]);

        try {
            app(DeleteStudentStatusAction::class)->handle($status);
            $this->fail('Used student status was deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('students.validation.dictionary_item_in_use'), $exception->errors()['record'][0]);
        }

        $this->assertDatabaseHas('student_statuses', ['id' => $status->id]);
    }

    public function test_enrollment_status_can_be_created(): void
    {
        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.enrollment-statuses', ['method' => 'save']), [
                'status' => $this->enrollmentStatusPayload('documents_verified'),
            ])
            ->assertRedirect(route('platform.students.enrollment-statuses'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollment_statuses', [
            'code' => 'documents_verified',
            'is_active' => true,
            'is_system' => false,
        ]);
    }

    public function test_enrollment_status_can_be_updated(): void
    {
        $status = EnrollmentStatus::factory()->create([
            'code' => 'practice_review',
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.enrollment-statuses', ['method' => 'save']), [
                'status' => $this->enrollmentStatusPayload('practice_review', [
                    'id' => $status->id,
                    'color' => '#7c3aed',
                    'sort_order' => 77,
                    'is_in_progress' => '1',
                    'name_translations' => $this->translations('Practice review'),
                ]),
            ])
            ->assertRedirect(route('platform.students.enrollment-statuses'))
            ->assertSessionHasNoErrors();

        $status->refresh();

        $this->assertSame('#7c3aed', $status->color);
        $this->assertSame(77, $status->sort_order);
        $this->assertTrue($status->is_in_progress);
        $this->assertSame('Practice review', $status->getTranslation('name', 'en'));
    }

    public function test_only_one_default_enrollment_status_exists(): void
    {
        $this->actingAs($this->userWithPermissions(['students.manage_statuses']))
            ->post(route('platform.students.enrollment-statuses', ['method' => 'save']), [
                'status' => $this->enrollmentStatusPayload('fresh_enrollment_default', [
                    'is_default' => '1',
                ]),
            ])
            ->assertRedirect(route('platform.students.enrollment-statuses'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, EnrollmentStatus::query()->where('is_default', true)->count());
        $this->assertTrue(EnrollmentStatus::query()->where('code', 'fresh_enrollment_default')->firstOrFail()->is_default);
    }

    public function test_used_enrollment_status_cannot_be_deleted(): void
    {
        $status = EnrollmentStatus::factory()->create([
            'code' => 'used_enrollment_status',
            'is_system' => false,
        ]);
        StudentEnrollment::factory()->create(['status_id' => $status->id]);

        try {
            app(DeleteEnrollmentStatusAction::class)->handle($status);
            $this->fail('Used enrollment status was deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('students.validation.dictionary_item_in_use'), $exception->errors()['record'][0]);
        }

        $this->assertDatabaseHas('enrollment_statuses', ['id' => $status->id]);
    }

    public function test_dictionary_display_names_use_current_locale(): void
    {
        $studentStatus = StudentStatus::factory()->create([
            'code' => 'localized_student_status',
            'name_translations' => [
                'ru' => 'Локальный статус',
                'en' => 'Localized status',
                'lt' => 'Lokalizuota busena',
                'pl' => 'Status lokalny',
            ],
        ]);
        $enrollmentStatus = EnrollmentStatus::factory()->create([
            'code' => 'localized_enrollment_status',
            'name_translations' => [
                'ru' => 'Локальный статус обучения',
                'en' => 'Localized enrollment status',
                'lt' => 'Lokalizuota registracijos busena',
                'pl' => 'Lokalny status zapisu',
            ],
        ]);

        app()->setLocale('pl');

        $this->assertSame('Status lokalny', $studentStatus->display_name);
        $this->assertSame('Lokalny status zapisu', $enrollmentStatus->display_name);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function studentStatusPayload(string $code, array $overrides = []): array
    {
        return [
            'code' => $code,
            'name' => str($code)->replace('_', ' ')->title()->toString(),
            'name_translations' => $this->translations(str($code)->replace('_', ' ')->title()->toString()),
            'description_translations' => $this->translations('Student status.'),
            'color' => '#2563eb',
            'sort_order' => 50,
            'is_default' => '0',
            'is_active' => '1',
            'is_final' => '0',
            'is_blocked' => '0',
            'is_archived' => '0',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function enrollmentStatusPayload(string $code, array $overrides = []): array
    {
        return [
            'code' => $code,
            'name' => str($code)->replace('_', ' ')->title()->toString(),
            'name_translations' => $this->translations(str($code)->replace('_', ' ')->title()->toString()),
            'description_translations' => $this->translations('Enrollment status.'),
            'color' => '#2563eb',
            'sort_order' => 50,
            'is_default' => '0',
            'is_active' => '1',
            'is_final' => '0',
            'is_success' => '0',
            'is_cancelled' => '0',
            'is_waiting_documents' => '0',
            'is_waiting_payment' => '0',
            'is_in_progress' => '0',
            ...$overrides,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }

    private function superadmin(): User
    {
        return User::factory()->create([
            'permissions' => SuperadminPermissions::enabled(),
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
