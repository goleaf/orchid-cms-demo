<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingGroupStatusTransitionRule implements ValidationRule
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED = [
        'draft' => ['recruiting', 'scheduled', 'cancelled'],
        'recruiting' => ['almost_full', 'full', 'closed', 'scheduled', 'active', 'cancelled'],
        'almost_full' => ['recruiting', 'full', 'closed', 'scheduled', 'active', 'cancelled'],
        'full' => ['closed', 'scheduled', 'active', 'cancelled'],
        'closed' => ['recruiting', 'scheduled', 'cancelled'],
        'scheduled' => ['active', 'cancelled', 'paused'],
        'active' => ['paused', 'completed', 'cancelled'],
        'paused' => ['active', 'completed', 'cancelled'],
        'completed' => ['archived'],
        'cancelled' => ['archived'],
    ];

    public function __construct(
        private readonly ?TrainingGroup $group = null,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->group?->exists || ! filled($value) || $this->canOverride()) {
            return;
        }

        $from = $this->group->statusRecord?->code ?? $this->group->status?->value;
        $to = $this->statusCode($value);

        if ($from === null || $to === null || $from === $to) {
            return;
        }

        if (in_array($to, self::ALLOWED[$from] ?? [], true)) {
            return;
        }

        $fail(tkey('education.groups.validation.invalid_status_transition'));
    }

    private function statusCode(mixed $value): ?string
    {
        if ($value instanceof TrainingGroupStatus) {
            return $value->code;
        }

        if (is_numeric($value)) {
            return TrainingGroupStatus::query()->whereKey($value)->value('code');
        }

        return filled($value) ? (string) $value : null;
    }

    private function canOverride(): bool
    {
        return $this->allowOverride
            || ($this->user?->hasAccess('education.groups.override_status_transition') ?? false);
    }
}
