<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Rules\ExamSessionCapacityRule;
use App\Rules\StudentCanJoinExamSessionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStudentToExamSessionRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.manage_sessions']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $session = filled($this->input('exam_session_id')) ? ExamSession::query()->find($this->input('exam_session_id')) : null;
        $enrollment = filled($this->input('enrollment_id')) ? StudentEnrollment::query()->find($this->input('enrollment_id')) : null;

        return [
            'exam_session_id' => ['required', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCapacityRule($session, $this->boolean('allow_overbooking'))],
            'student_id' => ['required', 'integer', Rule::exists(Student::class, 'id'), new StudentCanJoinExamSessionRule($session, $enrollment, $this->boolean('allow_overbooking'))],
            'enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'admitted' => ['nullable', 'boolean'],
            'block_reason' => ['nullable', 'string', 'max:2000'],
            'allow_overbooking' => ['nullable', 'boolean'],
        ];
    }
}
