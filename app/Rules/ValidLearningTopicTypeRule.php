<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLearningTopicTypeRule implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    public const TYPES = ['theory', 'practice', 'simulator', 'exam_preparation', 'other'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && ! in_array((string) $value, self::TYPES, true)) {
            $fail(tkey('education.validation.invalid_learning_topic_type'));
        }
    }
}
