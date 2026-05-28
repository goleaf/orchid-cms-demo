<?php

namespace App\Models;

use Database\Factories\StudentEnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollment extends Enrollment
{
    protected $table = 'enrollments';

    protected static function newFactory(): Factory
    {
        return StudentEnrollmentFactory::new();
    }
}
