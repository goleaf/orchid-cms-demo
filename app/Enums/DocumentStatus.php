<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Missing = 'missing';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case Expired = 'expired';
}
