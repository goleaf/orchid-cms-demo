<?php

namespace App\Rules;

use App\Services\Exams\ExamWorkflowService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ExamResultScoreRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly mixed $maxScore = null) {}

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
        if (! filled($value)) {
            return;
        }

        $maxScore = $this->maxScore ?? data_get($this->data, 'max_score') ?? data_get($this->data, 'result.max_score') ?? data_get($this->data, 'attempt.max_score');

        if (! app(ExamWorkflowService::class)->resultScoreValid($value, $maxScore)) {
            $fail(tkey('exams.validation.result_score_invalid'));
        }
    }
}
