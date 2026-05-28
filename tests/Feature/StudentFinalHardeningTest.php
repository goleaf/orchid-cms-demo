<?php

namespace Tests\Feature;

use App\Actions\ConvertLeadToStudentAction;
use App\Actions\LinkLeadToExistingStudentAction;
use App\Enums\GroupStatus;
use App\Enums\LeadStatus;
use App\Models\Course;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Rules\EnrollmentNotDuplicateForStudentRule;
use App\Rules\ExistingStudentCanBeUsedForConversionRule;
use App\Rules\LeadCanConvertToStudentRule;
use App\Rules\LeadNotAlreadyConvertedRule;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\StudentTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentFinalHardeningTest extends TestCase
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

    public function test_required_block_three_artifacts_exist(): void
    {
        foreach ([
            \Database\Factories\StudentStatusFactory::class,
            \Database\Factories\EnrollmentStatusFactory::class,
            \Database\Factories\StudentFactory::class,
            \Database\Factories\StudentEnrollmentFactory::class,
            \Database\Factories\StudentActivityFactory::class,
            \Database\Factories\StudentTaskFactory::class,
            \Database\Seeders\StudentStatusSeeder::class,
            \Database\Seeders\EnrollmentStatusSeeder::class,
            \Database\Seeders\StudentTranslationSeeder::class,
            \App\Actions\GenerateStudentNumberAction::class,
            \App\Actions\GenerateEnrollmentNumberAction::class,
            \App\Actions\NormalizeStudentPhoneAction::class,
            \App\Actions\FindMatchingStudentsAction::class,
            \App\Actions\CreateStudentAction::class,
            \App\Actions\UpdateStudentAction::class,
            \App\Actions\ArchiveStudentAction::class,
            \App\Actions\ChangeStudentStatusAction::class,
            \App\Actions\CreateStudentEnrollmentAction::class,
            \App\Actions\UpdateStudentEnrollmentAction::class,
            \App\Actions\ChangeEnrollmentStatusAction::class,
            \App\Actions\AssignStudentManagerAction::class,
            \App\Actions\AssignEnrollmentGroupAction::class,
            \App\Actions\AddStudentToTrainingGroupAction::class,
            \App\Actions\AddStudentNoteAction::class,
            \App\Actions\CreateStudentTaskAction::class,
            \App\Actions\CompleteStudentTaskAction::class,
            \App\Actions\CancelStudentTaskAction::class,
            \App\Actions\CreateStudentOnboardingTasksAction::class,
            \App\Actions\PrepareStudentDocumentsPlaceholderAction::class,
            \App\Actions\PrepareStudentPaymentPlaceholderAction::class,
            \App\Actions\CreatePortalAccessPlaceholderAction::class,
            \App\Actions\ValidateLeadForStudentConversionAction::class,
            \App\Actions\FindStudentMatchesForLeadAction::class,
            \App\Actions\PrepareLeadConversionDataAction::class,
            \App\Actions\ConvertLeadToStudentAction::class,
            \App\Actions\LinkLeadToExistingStudentAction::class,
            \App\Actions\MarkLeadAsConvertedAction::class,
            \App\Actions\BuildLeadConversionWarningsAction::class,
            \App\Http\Requests\Students\StoreStudentRequest::class,
            \App\Http\Requests\Students\UpdateStudentRequest::class,
            \App\Http\Requests\Students\StoreStudentEnrollmentRequest::class,
            \App\Http\Requests\Students\UpdateStudentEnrollmentRequest::class,
            \App\Http\Requests\Students\ConvertLeadToStudentRequest::class,
            \App\Rules\StudentPhoneOrEmailRequiredRule::class,
            \App\Rules\UniqueStudentContactRule::class,
            \App\Rules\StudentCanBeArchivedRule::class,
            \App\Rules\StudentCanBeUpdatedRule::class,
            \App\Rules\ValidStudentStatusTransitionRule::class,
            \App\Rules\ValidEnrollmentStatusTransitionRule::class,
            \App\Rules\StudentEnrollmentCanBeUpdatedRule::class,
            \App\Rules\EnrollmentCanJoinGroupRule::class,
            \App\Rules\ActiveStudentStatusRule::class,
            \App\Rules\ActiveEnrollmentStatusRule::class,
            \App\Rules\ValidStudentTaskStatusRule::class,
            \App\Rules\ValidStudentTaskPriorityRule::class,
            \App\Rules\ValidTrainingLanguageRule::class,
            \App\Rules\ValidGearboxTypeRule::class,
            \App\Rules\ValidTrainingFormatRule::class,
            \App\Rules\LeadCanConvertToStudentRule::class,
            \App\Rules\LeadNotAlreadyConvertedRule::class,
            \App\Rules\ExistingStudentCanBeUsedForConversionRule::class,
            \App\Rules\EnrollmentNotDuplicateForStudentRule::class,
            \App\Orchid\Screens\School\StudentListScreen::class,
            \App\Orchid\Screens\School\StudentEditScreen::class,
            \App\Orchid\Screens\School\StudentEnrollmentEditScreen::class,
            \App\Orchid\Screens\School\LeadConvertToStudentScreen::class,
            \App\Orchid\Screens\School\StudentTaskListScreen::class,
            \App\Orchid\Screens\School\StudentStatusListScreen::class,
            \App\Orchid\Screens\School\EnrollmentStatusListScreen::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_lead_can_link_to_existing_student_and_records_conversion(): void
    {
        $manager = User::factory()->create();
        $student = Student::factory()->active()->create([
            'first_name' => 'Existing',
            'last_name' => 'Student',
        ]);
        $course = Course::factory()->active()->visibleOnSite()->create();
        $lead = MarketingLead::factory()->create([
            'status' => LeadStatus::ReadyToEnroll,
            'source' => 'phone',
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'phone' => '+370 600 77111',
        ]);

        $result = app(LinkLeadToExistingStudentAction::class)->handle($lead, $student->id, [
            'training_program_id' => $course->id,
        ], $manager);

        $lead = $lead->refresh();

        $this->assertTrue($result['student']->is($student));
        $this->assertSame($student->id, $lead->converted_student_profile_id);
        $this->assertSame($result['enrollment']->id, $lead->converted_enrollment_id);
        $this->assertNotNull($lead->converted_at);
        $this->assertTrue($lead->activities()->where('type', 'converted')->exists());
        $this->assertTrue($student->activities()->where('type', 'created_from_lead')->exists());
    }

    public function test_conversion_rolls_back_when_group_join_fails(): void
    {
        $manager = User::factory()->create();
        $course = Course::factory()->active()->visibleOnSite()->create();
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'status' => GroupStatus::Recruiting,
            'capacity' => 1,
            'places_taken' => 1,
        ]);
        $lead = MarketingLead::factory()->create([
            'status' => LeadStatus::Contacted,
            'source' => 'phone',
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
            'training_group_id' => $group->id,
            'phone' => '+370 600 77222',
        ]);
        $studentCount = Student::query()->count();
        $enrollmentCount = StudentEnrollment::query()->count();

        try {
            app(ConvertLeadToStudentAction::class)->handle($lead, null, [], [], true, true, true, $manager);
            $this->fail('Conversion should fail for a full group.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('students.validation.enrollment_cannot_join_group'), $exception->errors()['training_group_id'][0]);
        }

        $this->assertSame($studentCount, Student::query()->count());
        $this->assertSame($enrollmentCount, StudentEnrollment::query()->count());
        $this->assertNull($lead->refresh()->converted_at);
        $this->assertNull($lead->converted_student_profile_id);
        $this->assertNull($lead->converted_enrollment_id);
        $this->assertSame(1, $group->refresh()->places_taken);
    }

    public function test_conversion_rules_return_translated_messages(): void
    {
        $student = Student::factory()->archived()->create();
        $course = Course::factory()->active()->visibleOnSite()->create();
        $convertedLead = MarketingLead::factory()->create([
            'status' => LeadStatus::Enrolled,
            'converted_at' => now(),
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
        ]);
        $spamLead = MarketingLead::factory()->create([
            'status' => LeadStatus::Spam,
            'training_program_id' => $course->id,
            'course_category_id' => $course->course_category_id,
        ]);
        $activeStudent = Student::factory()->active()->create();
        StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $activeStudent->id,
            'training_program_id' => $course->id,
        ]);

        $validator = Validator::make(
            ['lead_id' => $convertedLead->id],
            ['lead_id' => [new LeadNotAlreadyConvertedRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('students.conversion.validation.lead_already_converted'), $validator->errors()->first('lead_id'));

        $validator = Validator::make(
            ['lead_id' => $spamLead->id],
            ['lead_id' => [new LeadCanConvertToStudentRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('students.conversion.validation.lead_is_spam'), $validator->errors()->first('lead_id'));

        $validator = Validator::make(
            ['existing_student_id' => $student->id],
            ['existing_student_id' => [new ExistingStudentCanBeUsedForConversionRule]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('students.conversion.validation.existing_student_invalid'), $validator->errors()->first('existing_student_id'));

        $validator = Validator::make(
            ['enrollment' => ['training_program_id' => $course->id]],
            ['enrollment' => [new EnrollmentNotDuplicateForStudentRule($activeStudent)]]
        );
        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('students.conversion.validation.duplicate_enrollment'), $validator->errors()->first('enrollment'));
    }
}
