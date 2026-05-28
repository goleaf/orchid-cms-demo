<?php

namespace App\Http\Requests\Analytics;

use App\Enums\DashboardWidgetType;
use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Rules\ActiveAnalyticsDashboardRule;
use App\Rules\AnalyticsCodeRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\DashboardWidgetConfigRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardWidgetRequest extends FormRequest
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
        $widgetId = $this->widgetId();

        return array_replace([
            'widget.id' => ['nullable', 'integer', Rule::exists(DashboardWidget::class, 'id')],
            'widget.analytics_dashboard_id' => [
                'required',
                'integer',
                Rule::exists(AnalyticsDashboard::class, 'id'),
                new ActiveAnalyticsDashboardRule,
                new AnalyticsPermissionRule($this->user(), 'analytics.preferences.manage'),
            ],
            'widget.code' => [
                'required',
                'string',
                'max:120',
                new AnalyticsCodeRule,
                Rule::unique(DashboardWidget::class, 'code')->ignore($widgetId),
            ],
            'widget.widget_type' => ['required', Rule::in(DashboardWidgetType::values())],
            'widget.title_translations' => ['required', 'array'],
            'widget.title_translations.*' => ['nullable', 'string', 'max:255'],
            'widget.description_translations' => ['nullable', 'array'],
            'widget.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'widget.config' => ['nullable', 'array', new DashboardWidgetConfigRule],
            'widget.width' => ['nullable', 'integer', 'min:1', 'max:12'],
            'widget.height' => ['nullable', 'integer', 'min:1', 'max:12'],
            'widget.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'widget.is_active' => ['nullable', 'boolean'],
        ], $this->analyticsFilterRules('widget.filters'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages([
            'widget.code.unique' => tkey('analytics.validation.invalid_code'),
            'widget.widget_type.in' => tkey('analytics.validation.invalid_widget_config'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function widgetData(): array
    {
        $data = $this->validated('widget');
        unset($data['id']);

        $data['config'] = $data['config'] ?? [];
        $data['filters'] = $data['filters'] ?? [];
        $data['width'] = (int) ($data['width'] ?? 3);
        $data['height'] = (int) ($data['height'] ?? 1);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }

    public function widgetId(): ?int
    {
        $id = $this->input('widget.id');

        return filled($id) ? (int) $id : null;
    }
}
