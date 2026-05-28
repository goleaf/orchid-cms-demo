<?php

namespace App\Rules;

use App\Models\TrainingGroupMembership;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupMembershipNotDuplicateRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly ?int $ignoreId = null) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $groupId = data_get($this->data, 'membership.training_group_id');
        $enrollmentId = data_get($this->data, 'membership.enrollment_id', $value);

        if (! filled($groupId) || ! filled($enrollmentId)) {
            return;
        }

        $exists = TrainingGroupMembership::query()
            ->where('training_group_id', $groupId)
            ->where('enrollment_id', $enrollmentId)
            ->when($this->ignoreId !== null, fn ($query) => $query->whereKeyNot($this->ignoreId))
            ->exists();

        if ($exists) {
            $fail(tkey('education.validation.duplicate_membership'));
        }
    }
}
