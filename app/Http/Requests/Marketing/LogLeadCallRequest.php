<?php

namespace App\Http\Requests\Marketing;

use App\Rules\ActiveLeadLostReasonRule;
use App\Rules\FutureFollowUpDateRule;
use App\Rules\ValidLeadCallResultRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class LogLeadCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['crm.leads.update', 'platform.marketing.leads', 'website.update_leads']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'call.result' => ['required', 'string', new ValidLeadCallResultRule],
            'call.duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'call.comment' => ['nullable', 'string', 'max:2000'],
            'call.next_follow_up_at' => ['nullable', 'date', new FutureFollowUpDateRule],
            'call.lost_reason_code' => ['nullable', 'string', new ActiveLeadLostReasonRule],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function callData(): array
    {
        return $this->validated('call');
    }

    public function nextFollowUpAt(): ?Carbon
    {
        $value = $this->validated('call.next_follow_up_at', null);

        return filled($value) ? Carbon::parse($value) : null;
    }
}
