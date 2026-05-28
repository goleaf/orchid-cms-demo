<?php

namespace Database\Seeders;

use App\Actions\RecalculateTrainingGroupCapacityAction;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use Illuminate\Database\Seeder;

class DemoTrainingGroupMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StudentDictionarySeeder::class,
            DemoTrainingGroupSeeder::class,
        ]);

        foreach (TrainingGroup::query()->where('code', 'like', 'DEMO-%')->orderBy('id')->get() as $index => $group) {
            $student = $this->student($index);
            $enrollment = $this->enrollment($student, $group, $index);
            $this->membership($student, $enrollment, $group);

            app(RecalculateTrainingGroupCapacityAction::class)->handle($group->refresh(), null, false);
        }
    }

    private function student(int $index): Student
    {
        $email = 'education-demo-student-'.($index + 1).'@drivepro.test';
        $student = Student::factory()->active()->withConsent()->make([
            'student_number' => 'STU-DEMO-GROUP-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'email' => $email,
            'full_name' => 'Education Demo Student '.($index + 1),
            'first_name' => 'Education',
            'last_name' => 'Student '.($index + 1),
        ]);

        $attributes = $student->only($student->getFillable());
        unset($attributes['email']);

        return Student::query()->updateOrCreate(['email' => $email], $attributes);
    }

    private function enrollment(Student $student, TrainingGroup $group, int $index): StudentEnrollment
    {
        $number = 'ENR-DEMO-GROUP-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
        $enrollment = StudentEnrollment::factory()
            ->active()
            ->make([
                'enrollment_number' => $number,
                'student_profile_id' => $student->id,
                'training_program_id' => $group->training_program_id,
                'course_category_id' => $group->course_category_id,
                'branch_id' => $group->branch_id,
                'training_group_id' => $group->id,
            ]);

        $attributes = $enrollment->only($enrollment->getFillable());
        unset($attributes['enrollment_number']);

        return StudentEnrollment::query()->updateOrCreate(['enrollment_number' => $number], $attributes);
    }

    private function membership(Student $student, StudentEnrollment $enrollment, TrainingGroup $group): TrainingGroupMembership
    {
        $membership = TrainingGroupMembership::factory()
            ->active()
            ->make([
                'training_group_id' => $group->id,
                'student_profile_id' => $student->id,
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'student_enrollment_id' => $enrollment->id,
            ]);

        $attributes = $membership->only($membership->getFillable());
        unset($attributes['training_group_id'], $attributes['enrollment_id']);

        return TrainingGroupMembership::query()->updateOrCreate(
            [
                'training_group_id' => $group->id,
                'enrollment_id' => $enrollment->id,
            ],
            $attributes,
        );
    }
}
