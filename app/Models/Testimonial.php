<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Testimonial extends StudentReview
{
    protected $table = 'student_reviews';

    protected static function newFactory(): Factory
    {
        return TestimonialFactory::new();
    }
}
