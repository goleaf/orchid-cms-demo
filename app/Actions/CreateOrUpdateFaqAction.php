<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Models\Faq;

class CreateOrUpdateFaqAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?Faq $faq, array $attributes): Faq
    {
        $faq ??= new Faq;
        $attributes = $this->assignSortablePosition($faq, $attributes);

        $faq->fill($attributes);
        $faq->save();

        return $faq->refresh();
    }
}
