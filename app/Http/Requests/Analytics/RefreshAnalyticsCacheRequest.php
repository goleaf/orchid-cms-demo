<?php

namespace App\Http\Requests\Analytics;

use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\Branch;
use App\Models\User;
use App\Rules\AnalyticsCacheKeyRule;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\AnalyticsModuleAvailableRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\ValidKpiPeriodRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefreshAnalyticsCacheRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.cache.view');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_replace([
            'cache_key' => [
                'required',
                'string',
                'max:180',
                new AnalyticsCacheKeyRule,
                new AnalyticsPermissionRule($this->user(), 'analytics.cache.view'),
            ],
            'data' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:80', new AnalyticsCacheKeyRule],
            'ttl_seconds' => ['nullable', 'integer', 'min:60', 'max:604800'],
            'ttl_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'expires_at' => ['nullable', 'date'],
            'module' => ['nullable', 'string', 'max:80', new AnalyticsModuleAvailableRule],
            'period_type' => ['nullable', 'string', new ValidKpiPeriodRule],
            'period_start' => ['nullable', 'date', new AnalyticsDateRangeRule('period_start', 'period_end')],
            'period_end' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
        ], $this->analyticsFilterRules('filters'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages();
    }

    /**
     * @return array<string, mixed>
     */
    public function cacheData(): array
    {
        $data = $this->validated();
        $data['data'] = $data['data'] ?? [];
        $data['tags'] = $data['tags'] ?? [];
        $data['filters'] = $data['filters'] ?? [];

        return $data;
    }
}
