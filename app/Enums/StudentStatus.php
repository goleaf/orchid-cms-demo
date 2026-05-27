<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Lead = 'lead';
    case Enrolled = 'enrolled';
    case Graduated = 'graduated';
    case Archived = 'archived';
}
