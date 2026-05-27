<?php

namespace App\Rules;

use App\Models\LeadTag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveLeadTagRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $query = LeadTag::query()->active();
        is_numeric($value)
            ? $query->whereKey((int) $value)
            : $query->where('slug', (string) $value);

        if ($query->exists()) {
            return;
        }

        $fail(tkey('crm.leads.validation.tag_not_active'));
    }
}
