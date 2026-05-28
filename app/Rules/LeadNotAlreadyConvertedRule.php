<?php

namespace App\Rules;

use App\Models\MarketingLead;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadNotAlreadyConvertedRule implements ValidationRule
{
    public function __construct(private readonly ?MarketingLead $lead = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lead = $this->lead ?? $this->resolveLead($value);

        if ($lead?->is_converted) {
            $fail(tkey('students.conversion.validation.lead_already_converted'));
        }
    }

    private function resolveLead(mixed $value): ?MarketingLead
    {
        return filled($value)
            ? MarketingLead::query()->whereKey($value)->first()
            : null;
    }
}
