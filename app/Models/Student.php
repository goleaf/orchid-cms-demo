<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Student extends StudentProfile
{
    protected $table = 'student_profiles';

    protected static function newFactory(): Factory
    {
        return StudentFactory::new();
    }
}
