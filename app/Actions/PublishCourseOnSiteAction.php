<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\TrainingProgram;
use App\Rules\PublicCourseCanBePublishedRule;
use Illuminate\Support\Facades\Validator;

class PublishCourseOnSiteAction
{
    public function handle(Course|TrainingProgram $course): TrainingProgram
    {
        Validator::make(
            ['course' => $course->getKey()],
            ['course' => [new PublicCourseCanBePublishedRule]],
        )->validate();

        $course->forceFill([
            'is_active' => true,
            'is_visible_on_site' => true,
        ])->save();

        return $course->refresh();
    }
}
