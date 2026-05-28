<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Rules\ActiveExamTypeRule;
use App\Rules\EnrollmentCanTakeExamRule;
use App\Rules\InternalExamPassedRule;
use App\Rules\RequiredDocumentsAcceptedRule;
use App\Rules\RequiredPaymentsCompletedRule;
use App\Rules\RequiredPracticeHoursRule;
use App\Rules\RequiredTheoryHoursRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckExamAdmissionRequest extends FormRequest
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
        $type = $this->input('exam_type_id');

        return [
            'enrollment_id' => [
                'required',
                'integer',
                Rule::exists(StudentEnrollment::class, 'id'),
                new RequiredDocumentsAcceptedRule,
                new RequiredPaymentsCompletedRule,
                new RequiredTheoryHoursRule,
                new RequiredPracticeHoursRule,
                new InternalExamPassedRule($type),
                new EnrollmentCanTakeExamRule($type),
            ],
            'exam_type_id' => ['required', 'integer', Rule::exists(ExamType::class, 'id'), new ActiveExamTypeRule],
        ];
    }
}
