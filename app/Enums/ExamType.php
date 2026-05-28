<?php

namespace App\Enums;

enum ExamType: string
{
    case InternalTheory = 'internal_theory';
    case InternalPractical = 'internal_practical';
    case StateTheory = 'state_theory';
    case StatePractical = 'state_practical';

    public function provider(): string
    {
        return str_starts_with($this->value, 'state_') ? 'state' : 'internal';
    }

    public function isPractical(): bool
    {
        return in_array($this, [
            self::InternalPractical,
            self::StatePractical,
        ], true);
    }

    public function isTheory(): bool
    {
        return ! $this->isPractical();
    }
}
