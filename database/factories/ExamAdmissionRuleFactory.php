<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ExamAdmissionRule;
use App\Models\ExamType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAdmissionRule>
 */
class ExamAdmissionRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_type_id' => ExamType::factory()->internalTheory(),
            'course_id' => Course::factory(),
            'course_category_id' => CourseCategory::factory(),
            'required_theory_hours' => 40,
            'required_practice_hours' => 30,
            'require_documents' => true,
            'require_no_debt' => true,
            'require_internal_exam_passed' => false,
            'is_active' => true,
        ];
    }

    public function forExamType(ExamType $type): static
    {
        return $this->state(fn (): array => ['exam_type_id' => $type->id]);
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (): array => ['course_id' => $course->id]);
    }

    public function forCourseCategory(CourseCategory $category): static
    {
        return $this->state(fn (): array => ['course_category_id' => $category->id]);
    }
}
