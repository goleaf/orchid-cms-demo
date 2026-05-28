<?php

namespace App\Http\Requests\Exams;

use App\Enums\ExamSessionStatus;
use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\Branch;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\Vehicle;
use App\Rules\ValidExamTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamSessionRequest extends FormRequest
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
        return [
            'session.id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id')],
            'session.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'session.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'session.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'session.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'session.vehicle_id' => ['nullable', 'integer', Rule::exists(Vehicle::class, 'id')],
            'session.exam_type' => ['required', 'string', new ValidExamTypeRule],
            'session.provider' => ['nullable', 'string', 'max:40'],
            'session.status' => ['nullable', Rule::enum(ExamSessionStatus::class)],
            'session.starts_at' => ['required', 'date'],
            'session.ends_at' => ['nullable', 'date', 'after:session.starts_at'],
            'session.location' => ['nullable', 'string', 'max:255'],
            'session.capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'session.seats_taken' => ['nullable', 'integer', 'min:0', 'lte:session.capacity'],
            'session.external_reference' => ['nullable', 'string', 'max:120'],
            'session.official_placeholder_payload' => ['nullable', 'array'],
            'session.notes' => ['nullable', 'string', 'max:2000'],
            'session.internal_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sessionData(): array
    {
        $data = $this->validated('session');

        foreach (['id', 'branch_id', 'training_program_id', 'training_group_id', 'instructor_id', 'vehicle_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filled($data[$field]) ? (int) $data[$field] : null;
            }
        }

        return $data;
    }
}
