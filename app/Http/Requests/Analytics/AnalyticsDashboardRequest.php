<?php

namespace App\Http\Requests\Analytics;

use App\Enums\AnalyticsDashboardAudience;
use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\AnalyticsDashboard;
use App\Rules\AnalyticsCodeRule;
use App\Rules\AnalyticsPermissionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsDashboardRequest extends FormRequest
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
        $dashboardId = $this->dashboardId();

        return [
            'dashboard.id' => ['nullable', 'integer', Rule::exists(AnalyticsDashboard::class, 'id')],
            'dashboard.code' => [
                'required',
                'string',
                'max:120',
                new AnalyticsCodeRule,
                Rule::unique(AnalyticsDashboard::class, 'code')->ignore($dashboardId),
            ],
            'dashboard.name_translations' => ['required', 'array'],
            'dashboard.name_translations.*' => ['nullable', 'string', 'max:255'],
            'dashboard.description_translations' => ['nullable', 'array'],
            'dashboard.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'dashboard.audience' => [
                'required',
                Rule::in(AnalyticsDashboardAudience::values()),
                new AnalyticsPermissionRule($this->user(), 'analytics.preferences.manage'),
            ],
            'dashboard.is_active' => ['nullable', 'boolean'],
            'dashboard.is_default' => ['nullable', 'boolean'],
            'dashboard.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages([
            'dashboard.code.unique' => tkey('analytics.validation.invalid_code'),
            'dashboard.audience.in' => tkey('analytics.validation.invalid_dashboard'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $data = $this->validated('dashboard');
        unset($data['id']);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    public function dashboardId(): ?int
    {
        $id = $this->input('dashboard.id');

        return filled($id) ? (int) $id : null;
    }
}
