<?php

namespace App\Actions;

use App\Models\MarketingLead;
use Illuminate\Http\UploadedFile;

class CreateEnrollmentLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $documents
     */
    public function handle(array $data, array $documents = []): MarketingLead
    {
        return app(CreateWebsiteLeadAction::class)->handle($data, null, $documents);
    }
}
