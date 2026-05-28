<?php

namespace App\Rules;

use App\Actions\FindMatchingStudentsAction;
use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueStudentContactRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly ?Student $ignore = null,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
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
        if ($this->allowOverride || ($this->user?->hasAccess('students.override_duplicate_contact') ?? false)) {
            return;
        }

        $student = data_get($this->data, 'student', []);
        $payload = is_array($student) ? $student : $this->data;

        if (app(FindMatchingStudentsAction::class)->handle($payload, $this->ignore)->isNotEmpty()) {
            $fail(tkey('students.validation.duplicate_contact'));
        }
    }
}
