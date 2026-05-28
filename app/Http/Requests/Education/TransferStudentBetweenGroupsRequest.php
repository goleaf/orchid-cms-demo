<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Rules\GroupMembershipCanBeTransferredRule;
use App\Rules\TrainingGroupOpenForEnrollmentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentBetweenGroupsRequest extends FormRequest
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
            'membership_id' => ['required', 'integer', Rule::exists(TrainingGroupMembership::class, 'id'), new GroupMembershipCanBeTransferredRule],
            'target_group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id'), new TrainingGroupOpenForEnrollmentRule($this->boolean('allow_overbooking'))],
            'allow_overbooking' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
