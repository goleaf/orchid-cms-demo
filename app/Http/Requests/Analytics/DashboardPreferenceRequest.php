<?php

namespace App\Http\Requests\Analytics;

use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Rules\ActiveAnalyticsDashboardRule;
use App\Rules\DashboardWidgetCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DashboardPreferenceRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.preferences.manage');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences.analytics_dashboard_id' => ['nullable', new ActiveAnalyticsDashboardRule],
            'preferences.dashboard' => ['nullable', 'string', 'max:80', new ActiveAnalyticsDashboardRule],
            'preferences.layout' => ['nullable', 'array'],
            'preferences.layout.widgets' => ['nullable', 'array'],
            'preferences.layout.widgets.*.code' => ['required_with:preferences.layout.widgets', 'string', new DashboardWidgetCodeRule],
            'preferences.layout.widgets.*.width' => ['nullable', 'integer', 'min:1', 'max:12'],
            'preferences.layout.widgets.*.height' => ['nullable', 'integer', 'min:1', 'max:12'],
            'preferences.layout.widgets.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'preferences.visible_widget_codes' => ['nullable', 'array'],
            'preferences.visible_widget_codes.*' => ['string', new DashboardWidgetCodeRule],
            'preferences.widget_order' => ['nullable', 'array'],
            'preferences.widget_order.*' => ['string', new DashboardWidgetCodeRule],
            ...$this->analyticsFilterRules('preferences.filters'),
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages();
    }
}
