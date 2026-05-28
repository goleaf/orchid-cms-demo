<?php

namespace App\Enums;

enum KpiSnapshotStatus: string
{
    case OnTrack = 'on_track';
    case Warning = 'warning';
    case OffTrack = 'off_track';
    case Neutral = 'neutral';
}
