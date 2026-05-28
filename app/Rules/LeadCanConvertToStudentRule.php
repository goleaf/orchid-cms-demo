<?php

namespace App\Rules;

use App\Actions\ValidateLeadForStudentConversionAction;
use App\Models\MarketingLead;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeadCanConvertToStudentRule implements ValidationRule
{
    /**
     * @param  array<string, mixed>  $enrollmentData
     */
    public function __construct(
        private readonly ?MarketingLead $lead = null,
        private readonly ?User $user = null,
        private readonly array $enrollmentData = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lead = $this->lead ?? $this->resolveLead($value);

        if ($lead === null) {
            $fail(tkey('students.conversion.validation.lead_cannot_convert'));

            return;
        }

        $validation = app(ValidateLeadForStudentConversionAction::class)->handle($lead, $this->user, $this->enrollmentData);

        if (! $validation['can_convert']) {
            $fail(tkey($validation['blocking_errors'][0] ?? 'students.conversion.validation.lead_cannot_convert'));
        }
    }

    private function resolveLead(mixed $value): ?MarketingLead
    {
        return filled($value)
            ? MarketingLead::query()->whereKey($value)->first()
            : null;
    }
}
