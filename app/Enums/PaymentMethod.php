<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum PaymentMethod: string
{
    use HasEnumValues;

    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return tkey('operations.payment_methods.'.$this->value);
    }
}
