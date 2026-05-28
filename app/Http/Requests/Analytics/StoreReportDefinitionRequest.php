<?php

namespace App\Http\Requests\Analytics;

use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;

class StoreReportDefinitionRequest extends ReportDefinitionRequest
{
    use UsesAnalyticsRequestValidation;

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages(parent::messages());
    }
}
