<?php

namespace App\Enums;

enum KpiSnapshotStatus: string
{
    case BelowTarget = 'below_target';
    case OnTrack = 'on_track';
    case Achieved = 'achieved';
    case Exceeded = 'exceeded';
    case Unknown = 'unknown';

    /** @deprecated Kept for legacy analytics snapshots. */
    case Warning = 'warning';

    /** @deprecated Kept for legacy analytics snapshots. */
    case OffTrack = 'off_track';

    /** @deprecated Kept for legacy analytics snapshots. */
    case Neutral = 'neutral';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::BelowTarget->value,
            self::OnTrack->value,
            self::Achieved->value,
            self::Exceeded->value,
            self::Unknown->value,
        ];
    }
}
