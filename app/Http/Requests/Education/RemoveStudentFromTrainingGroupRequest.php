<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroupMembership;
use App\Rules\GroupMembershipCanBeRemovedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveStudentFromTrainingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess([
            'education.groups.manage_students',
            'education.manage_memberships',
        ]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'membership_id' => ['required', 'integer', Rule::exists(TrainingGroupMembership::class, 'id'), new GroupMembershipCanBeRemovedRule],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
