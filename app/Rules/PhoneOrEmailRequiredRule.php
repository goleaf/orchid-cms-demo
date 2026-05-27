<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneOrEmailRequiredRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly string $phoneField = 'phone',
        private readonly string $emailField = 'email',
        private readonly string $messageKey = 'website.validation.phone_or_email_required',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled(data_get($this->data, $this->phoneField)) || filled(data_get($this->data, $this->emailField))) {
            return;
        }

        $fail(tkey($this->messageKey));
    }
}
