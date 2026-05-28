<?php

namespace App\Rules;

use App\Models\LeadStatus;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class OnlyOneDefaultLeadStatusRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly string $dictionary,
        private readonly ?int $recordId = null,
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
        if ($this->dictionary !== 'statuses') {
            return;
        }

        $isDefault = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $isActive = filter_var(data_get($this->data, 'item.is_active', false), FILTER_VALIDATE_BOOLEAN);

        if ($isDefault && ! $isActive) {
            $fail(tkey('crm.validation.dictionary_default_status_inactive'));

            return;
        }

        if (! $isDefault && $this->recordId !== null && ! $this->canClearCurrentDefault()) {
            $fail(tkey('crm.validation.dictionary_default_status_required'));
        }
    }

    private function canClearCurrentDefault(): bool
    {
        $status = LeadStatus::query()->find($this->recordId);

        if ($status === null || ! $status->is_default) {
            return true;
        }

        return LeadStatus::query()
            ->whereKeyNot($status->getKey())
            ->where('is_default', true)
            ->exists();
    }
}
