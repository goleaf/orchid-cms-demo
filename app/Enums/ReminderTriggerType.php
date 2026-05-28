<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum ReminderTriggerType: string
{
    use HasEnumValues;

    case BeforeLesson = 'before_lesson';
    case AfterSignup = 'after_signup';
    case BeforePaymentDue = 'before_payment_due';
    case BeforeExam = 'before_exam';
    case Manual = 'manual';

    public function label(): string
    {
        return tkey('notifications.reminder_triggers.'.$this->value);
    }
}
