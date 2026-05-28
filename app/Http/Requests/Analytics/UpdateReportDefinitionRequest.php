<?php

namespace App\Http\Requests\Analytics;

use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;

class UpdateReportDefinitionRequest extends ReportDefinitionRequest
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
