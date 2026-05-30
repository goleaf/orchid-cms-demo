<?php

namespace App\Http\Requests\Security;

class ExportLoginAttemptsRequest extends FilterLoginAttemptsRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('security.login_attempts.export') ?? false;
    }
}
