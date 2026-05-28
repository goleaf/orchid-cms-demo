<?php

namespace App\Http\Requests\Analytics;

use App\Rules\DashboardWidgetCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DashboardPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.preferences.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences.dashboard' => ['nullable', 'string', 'max:80'],
            'preferences.visible_widget_codes' => ['nullable', 'array'],
            'preferences.visible_widget_codes.*' => ['string', new DashboardWidgetCodeRule],
            'preferences.widget_order' => ['nullable', 'array'],
            'preferences.widget_order.*' => ['string', new DashboardWidgetCodeRule],
            'preferences.filters' => ['nullable', 'array'],
            'preferences.refresh_interval_seconds' => ['nullable', 'integer', 'min:60', 'max:3600'],
            'preferences.timezone' => ['nullable', 'string', 'timezone'],
            'preferences.is_default' => ['nullable', 'boolean'],
            'preferences.settings' => ['nullable', 'array'],
        ];
    }

    public function dashboard(): string
    {
        return (string) $this->validated('preferences.dashboard', 'owner');
    }

    /**
     * @return array<string, mixed>
     */
    public function preferenceData(): array
    {
        return $this->validated('preferences', []);
    }
}
