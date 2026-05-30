<?php

namespace App\Http\Requests\Security;

class ExportUserSecuritySessionsRequest extends FilterUserSecuritySessionsRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('security.sessions.export') ?? false;
    }
}
