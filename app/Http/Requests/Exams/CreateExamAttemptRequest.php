<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Rules\EnrollmentCanTakeExamRule;
use App\Rules\ExamSessionCapacityRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExamAttemptRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.record_results']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $session = filled($this->input('exam_session_id')) ? ExamSession::query()->find($this->input('exam_session_id')) : null;

        return [
            'exam_session_id' => ['required', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCapacityRule($session, $this->boolean('allow_overbooking'))],
            'student_id' => ['required', 'integer', Rule::exists(Student::class, 'id')],
            'enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id'), new EnrollmentCanTakeExamRule($session?->type_id)],
            'allow_overbooking' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
