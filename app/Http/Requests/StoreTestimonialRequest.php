<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\StudentProfile;
use App\Rules\TranslatedFieldRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['website.manage_pages', 'website.manage_courses', 'website.manage_branches']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', Rule::exists(Course::class, 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'student_id' => ['nullable', 'integer', Rule::exists(StudentProfile::class, 'id')],
            'name_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'name_translations.*' => ['nullable', 'string', 'max:255'],
            'text_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'text_translations.*' => ['nullable', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'image' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_translations.required' => tkey('website.validation.default_translation_required'),
            'text_translations.required' => tkey('website.validation.default_translation_required'),
        ];
    }
}
