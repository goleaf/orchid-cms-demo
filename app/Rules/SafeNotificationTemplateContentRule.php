<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeNotificationTemplateContentRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->values($value) as $content) {
            if ($this->isUnsafe($content)) {
                $fail(tkey('notifications.validation.unsafe_template_content'));

                return;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function values(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn (mixed $item): array => $this->values($item))
                ->all();
        }

        return is_scalar($value) ? [(string) $value] : [];
    }

    private function isUnsafe(string $content): bool
    {
        return preg_match('/<\s*(script|iframe|object|embed)\b/i', $content) === 1
            || preg_match('/\bon[a-z]+\s*=/i', $content) === 1
            || preg_match('/javascript\s*:/i', $content) === 1;
    }
}
