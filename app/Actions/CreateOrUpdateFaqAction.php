<?php

namespace App\Actions;

use App\Models\Faq;

class CreateOrUpdateFaqAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?Faq $faq, array $attributes): Faq
    {
        $faq ??= new Faq;
        $faq->fill($attributes);
        $faq->save();

        return $faq->refresh();
    }
}
