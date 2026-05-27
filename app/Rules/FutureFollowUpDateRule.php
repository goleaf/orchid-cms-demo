<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Throwable;

class FutureFollowUpDateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        try {
            $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        } catch (Throwable) {
            $fail(tkey('crm.leads.validation.follow_up_must_be_future'));

            return;
        }

        if ($date->isPast()) {
            $fail(tkey('crm.leads.validation.follow_up_must_be_future'));
        }
    }
}
