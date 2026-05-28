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

        if (! $faq->exists && ! array_key_exists('sort_order', $attributes)) {
            $attributes['sort_order'] = ((int) Faq::query()->max('sort_order')) + 10;
        }

        $faq->fill($attributes);
        $faq->save();

        return $faq->refresh();
    }
}
