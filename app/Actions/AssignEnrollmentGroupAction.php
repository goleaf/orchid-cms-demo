<?php

namespace App\Actions;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;

class AssignEnrollmentGroupAction
{
    public function handle(StudentEnrollment $enrollment, TrainingGroup|int $group, ?User $user = null, bool $allowOverbooking = false): StudentEnrollment
    {
        app(AddStudentToTrainingGroupAction::class)->handle($enrollment, $group, $user, $allowOverbooking);

        return $enrollment->refresh();
    }
}
