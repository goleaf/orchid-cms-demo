<?php

namespace App\Rules;

use App\Models\MarketingMessageTemplate;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveMessageTemplateForChannel implements ValidationRule
{
    public function __construct(private readonly mixed $channel) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        if (
            MarketingMessageTemplate::query()
                ->active()
                ->forChannel(filled($this->channel) ? (string) $this->channel : null)
                ->whereKey($value)
                ->exists()
        ) {
            return;
        }

        $fail(tkey('crm.validation.message_template_unavailable'));
    }
}
