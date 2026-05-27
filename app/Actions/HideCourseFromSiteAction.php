<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\TrainingProgram;

class HideCourseFromSiteAction
{
    public function handle(Course|TrainingProgram $course): TrainingProgram
    {
        $course->forceFill(['is_visible_on_site' => false])->save();

        return $course->refresh();
    }
}
