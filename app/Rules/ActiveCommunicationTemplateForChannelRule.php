<?php

namespace App\Rules;

use App\Models\CommunicationTemplate;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveCommunicationTemplateForChannelRule implements ValidationRule
{
    public function __construct(private readonly mixed $channelId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $query = CommunicationTemplate::query()
            ->active()
            ->whereKey($value);

        if (filled($this->channelId)) {
            $query->forChannel((int) $this->channelId);
        }

        if ($query->exists()) {
            return;
        }

        $fail(tkey('communication.validation.template_unavailable'));
    }
}
