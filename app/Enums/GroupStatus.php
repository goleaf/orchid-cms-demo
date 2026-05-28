<?php

namespace App\Enums;

enum GroupStatus: string
{
    case Planned = 'planned';
    case Recruiting = 'recruiting';
    case Open = 'open';
    case AlmostFull = 'almost_full';
    case Closed = 'closed';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('education.groups.statuses.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }
}
