<?php

namespace App\Actions;

use App\Models\MarketingMessageTemplate;

class DeleteMarketingMessageTemplateAction
{
    public function handle(MarketingMessageTemplate $template): void
    {
        $template->delete();
    }
}
