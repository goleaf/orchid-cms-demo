<?php

namespace App\Http\Requests\Exams\Concerns;

trait UsesExamValidationMessages
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => tkey('exams.validation.required'),
            'required_without' => tkey('exams.validation.required'),
            'required_with' => tkey('exams.validation.required'),
            'integer' => tkey('exams.validation.integer'),
            'numeric' => tkey('exams.validation.numeric'),
            'boolean' => tkey('exams.validation.boolean'),
            'string' => tkey('exams.validation.string'),
            'array' => tkey('exams.validation.array'),
            'date' => tkey('exams.validation.date'),
            'after' => tkey('exams.validation.date_after'),
            'after_or_equal' => tkey('exams.validation.date_after'),
            'exists' => tkey('exams.validation.exists'),
            'min' => tkey('exams.validation.min'),
            'max' => tkey('exams.validation.max'),
            'lte' => tkey('exams.validation.max'),
        ];
    }
}
