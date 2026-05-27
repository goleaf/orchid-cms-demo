<?php

namespace App\Actions;

use App\Models\MarketingMessageTemplate;

class SaveMarketingMessageTemplateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(MarketingMessageTemplate $template, array $data): MarketingMessageTemplate
    {
        $template->fill($data);
        $template->save();

        return $template;
    }
}
