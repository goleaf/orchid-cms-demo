<?php

namespace App\Rules;

use App\Actions\Security\SanitizeLoginMetadataAction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class LoginMetadataSensitiveFieldRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        if ($this->hasUnredactedSensitiveValue($value)) {
            $fail(tkey('security.validation.login_metadata_sensitive_field_not_redacted'));
        }
    }

    /**
     * @param  array<mixed>  $values
     */
    private function hasUnredactedSensitiveValue(array $values): bool
    {
        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey((string) $key) && $value !== SanitizeLoginMetadataAction::REDACTED) {
                return true;
            }

            if (is_array($value) && $this->hasUnredactedSensitiveValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', '.', ' '], '_')->toString();

        foreach (SanitizeLoginMetadataAction::SENSITIVE_FIELDS as $field) {
            if ($normalized === $field || str_contains($normalized, $field)) {
                return true;
            }
        }

        return false;
    }
}
