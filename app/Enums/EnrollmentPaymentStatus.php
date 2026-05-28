<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum EnrollmentPaymentStatus: string
{
    use HasEnumValues;

    case NotRequired = 'not_required';
    case Pending = 'pending';
    case Waiting = 'waiting';
    case Partial = 'partial';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return tkey('students.payment_statuses.'.$this->value);
    }
}
