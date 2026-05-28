<?php

namespace App\Http\Requests\Education;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Rules\StudentEnrollmentCanJoinGroupRule;
use App\Rules\StudentEnrollmentNotAlreadyInActiveGroupRule;
use App\Rules\TrainingGroupCapacityRule;
use App\Rules\TrainingGroupOpenForEnrollmentRule;
use App\Rules\TrainingGroupMembershipNotDuplicateRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_memberships') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $membershipId = $this->integer('membership.id') ?: null;
        $allowOverbooking = $this->boolean('membership.allow_overbooking');

        return [
            'membership.id' => ['nullable', 'integer', Rule::exists(TrainingGroupMembership::class, 'id')],
            'membership.training_group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id'), new TrainingGroupOpenForEnrollmentRule($allowOverbooking), new TrainingGroupCapacityRule(null, $allowOverbooking)],
            'membership.enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id'), new StudentEnrollmentCanJoinGroupRule($this->group()), new StudentEnrollmentNotAlreadyInActiveGroupRule($membershipId), new TrainingGroupMembershipNotDuplicateRule($membershipId)],
            'membership.allow_overbooking' => ['nullable', 'boolean'],
            'membership.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function membershipData(): array
    {
        $data = $this->validated('membership');
        unset($data['id']);

        $data['allow_overbooking'] = (bool) ($data['allow_overbooking'] ?? false);

        return $data;
    }

    private function group(): ?TrainingGroup
    {
        $groupId = $this->input('membership.training_group_id');

        return filled($groupId) ? TrainingGroup::query()->find((int) $groupId) : null;
    }
}
