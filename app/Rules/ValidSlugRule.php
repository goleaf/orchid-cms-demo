<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ValidSlugRule implements ValidationRule
{
    /**
     * @param  class-string<Model>|null  $modelClass
     */
    public function __construct(
        private readonly ?string $modelClass = null,
        private readonly string $column = 'slug',
        private readonly mixed $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            $fail(tkey('website.validation.invalid_slug'));

            return;
        }

        if ($this->modelClass === null || ! is_a($this->modelClass, Model::class, true)) {
            return;
        }

        $exists = $this->modelClass::query()
            ->where($this->column, $value)
            ->when(filled($this->ignoreId), fn ($query) => $query->whereKeyNot($this->ignoreId))
            ->exists();

        if ($exists) {
            $fail(tkey('website.validation.invalid_slug'));
        }
    }
}
