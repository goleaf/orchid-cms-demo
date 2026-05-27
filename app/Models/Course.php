<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Course extends TrainingProgram
{
    protected $table = 'training_programs';

    protected static function newFactory(): Factory
    {
        return CourseFactory::new();
    }
}
