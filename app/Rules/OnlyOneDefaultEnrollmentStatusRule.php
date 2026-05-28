<?php

namespace App\Rules;

use App\Models\EnrollmentStatus;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class OnlyOneDefaultEnrollmentStatusRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly ?int $recordId = null,
        private readonly string $root = 'status',
    ) {}

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
        $isDefault = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $isActive = filter_var(data_get($this->data, $this->root.'.is_active', false), FILTER_VALIDATE_BOOLEAN);

        if ($isDefault && ! $isActive) {
            $fail(tkey('students.validation.default_status_inactive'));

            return;
        }

        if (! $isDefault && $this->recordId !== null && ! $this->canClearCurrentDefault()) {
            $fail(tkey('students.validation.default_status_required'));
        }
    }

    private function canClearCurrentDefault(): bool
    {
        $status = EnrollmentStatus::query()->find($this->recordId);

        if ($status === null || ! $status->is_default) {
            return true;
        }

        return EnrollmentStatus::query()
            ->whereKeyNot($status->getKey())
            ->where('is_default', true)
            ->exists();
    }
}
