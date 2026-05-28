<?php

namespace App\Actions;

use App\Support\Crm\PhoneNormalizer;

class NormalizeStudentPhoneAction
{
    public function handle(?string $phone): ?string
    {
        return PhoneNormalizer::normalize($phone);
    }
}
