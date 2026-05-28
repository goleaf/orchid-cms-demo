<?php

namespace App\Http\Requests\Exams;

use App\Enums\ExamAdmissionStatus;
use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\Branch;
use App\Models\ExamAdmission;
use App\Models\Instructor;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Rules\ValidExamTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamAdmissionRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.manage_admissions']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'admission.id' => ['nullable', 'integer', Rule::exists(ExamAdmission::class, 'id')],
            'admission.enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'admission.admission_type' => ['required', 'string', new ValidExamTypeRule],
            'admission.status' => ['nullable', Rule::enum(ExamAdmissionStatus::class)],
            'admission.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'admission.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'admission.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'admission.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'admission.required_theory_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'admission.completed_theory_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'admission.required_practice_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'admission.completed_practice_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'admission.documents_status' => ['nullable', 'string', 'max:40'],
            'admission.payment_status' => ['nullable', 'string', 'max:40'],
            'admission.checklist_status' => ['nullable', 'string', 'max:40'],
            'admission.admitted_at' => ['nullable', 'date'],
            'admission.rejected_at' => ['nullable', 'date'],
            'admission.expires_at' => ['nullable', 'date'],
            'admission.notes' => ['nullable', 'string', 'max:2000'],
            'admission.internal_notes' => ['nullable', 'string', 'max:2000'],
            'admission.checklist_items' => ['nullable', 'array'],
            'admission.checklist_items.*.code' => ['required_with:admission.checklist_items', 'string', 'max:80'],
            'admission.checklist_items.*.status' => ['nullable', 'string', 'max:40'],
            'admission.checklist_items.*.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function admissionData(): array
    {
        $data = $this->validated('admission');
        unset($data['enrollment_id']);

        foreach (['id', 'training_group_id', 'training_program_id', 'branch_id', 'instructor_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filled($data[$field]) ? (int) $data[$field] : null;
            }
        }

        return $data;
    }
}
