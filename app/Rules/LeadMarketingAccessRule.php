<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadMarketingAccessRule implements ValidationRule
{
    public function __construct(private readonly ?User $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if (
            $this->user?->hasAnyAccess([
                'crm.leads.view_marketing',
                'website.view_marketing',
            ])
        ) {
            return;
        }

        $fail(tkey('crm.leads.validation.marketing_access_denied'));
    }
}
