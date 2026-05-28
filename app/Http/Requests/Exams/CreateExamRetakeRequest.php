<?php

namespace App\Http\Requests\Exams;

use App\Models\DrivingLesson;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Rules\ExamAttemptCanBeRetakenRule;
use App\Rules\ExamSessionCanAcceptAttemptRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExamRetakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.schedule_retakes']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attempt.id' => ['required', 'integer', Rule::exists(ExamAttempt::class, 'id'), new ExamAttemptCanBeRetakenRule],
            'retake.exam_session_id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCanAcceptAttemptRule($this->boolean('allow_full_session'))],
            'retake.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'retake.driving_lesson_id' => ['nullable', 'integer', Rule::exists(DrivingLesson::class, 'id')],
            'retake.student_document_id' => ['nullable', 'integer', Rule::exists(StudentDocument::class, 'id')],
            'retake.payment_id' => ['nullable', 'integer', Rule::exists(Payment::class, 'id')],
            'retake.next_eligible_at' => ['nullable', 'date'],
            'retake.notes' => ['nullable', 'string', 'max:2000'],
            'retake.internal_notes' => ['nullable', 'string', 'max:2000'],
            'allow_full_session' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retakeData(): array
    {
        return $this->validated('retake') ?? [];
    }
}
