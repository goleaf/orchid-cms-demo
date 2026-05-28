<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum MarketingLeadCallResult: string
{
    use HasEnumValues;

    case Reached = 'reached';
    case Answered = 'answered';
    case NoAnswer = 'no_answer';
    case WrongNumber = 'wrong_number';
    case CallBackLater = 'call_back_later';
    case CallbackRequested = 'callback_requested';
    case Thinking = 'thinking';
    case ReadyToPay = 'ready_to_pay';
    case Refused = 'refused';
    case Lost = 'lost';

    public function label(): string
    {
        return tkey('crm.communications.call_results.'.$this->value);
    }
}
