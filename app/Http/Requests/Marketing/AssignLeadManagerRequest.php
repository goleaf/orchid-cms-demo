<?php

namespace App\Http\Requests\Marketing;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLeadManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.assign', 'crm.leads.update', 'platform.marketing.leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
        ];
    }

    public function managerId(): ?int
    {
        $managerId = $this->validated('manager_id', null);

        return filled($managerId) ? (int) $managerId : null;
    }
}
