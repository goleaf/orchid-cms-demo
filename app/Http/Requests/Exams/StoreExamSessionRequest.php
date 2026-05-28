<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\Branch;
use App\Models\ExamStatus;
use App\Models\ExamType;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vehicle;
use App\Rules\ActiveExamStatusRule;
use App\Rules\ActiveExamTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamSessionRequest extends FormRequest
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
            'type_id' => ['required', 'integer', Rule::exists(ExamType::class, 'id'), new ActiveExamTypeRule],
            'status_id' => ['nullable', 'integer', Rule::exists(ExamStatus::class, 'id'), new ActiveExamStatusRule],
            'branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'examiner_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'vehicle_id' => ['nullable', 'integer', Rule::exists(Vehicle::class, 'id')],
            'classroom_id' => ['nullable', 'integer', 'min:1'],
            'scheduled_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:scheduled_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sessionData(): array
    {
        return $this->validated();
    }
}
