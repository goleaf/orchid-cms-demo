<?php

namespace App\Rules;

use App\Models\CommunicationMessage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCommunicationDirectionRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, CommunicationMessage::directionValues(), true)) {
            return;
        }

        $fail(tkey('notifications.validation.invalid_communication_direction'));
    }
}
