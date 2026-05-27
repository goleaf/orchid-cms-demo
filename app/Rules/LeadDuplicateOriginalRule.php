<?php

namespace App\Rules;

use App\Models\MarketingLead;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadDuplicateOriginalRule implements ValidationRule
{
    public function __construct(private readonly mixed $currentLeadId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            $fail(tkey('crm.leads.validation.duplicate_original_required'));

            return;
        }

        if (filled($this->currentLeadId) && (int) $value === (int) $this->currentLeadId) {
            $fail(tkey('crm.leads.validation.cannot_duplicate_itself'));

            return;
        }

        if (! MarketingLead::query()->whereKey((int) $value)->exists()) {
            $fail(tkey('crm.leads.validation.duplicate_original_required'));
        }
    }
}
