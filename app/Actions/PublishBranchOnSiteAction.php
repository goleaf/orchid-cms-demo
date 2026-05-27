<?php

namespace App\Actions;

use App\Models\Branch;
use App\Rules\PublicBranchCanBePublishedRule;
use Illuminate\Support\Facades\Validator;

class PublishBranchOnSiteAction
{
    public function handle(Branch $branch): Branch
    {
        Validator::make(
            ['branch' => $branch->getKey()],
            ['branch' => [new PublicBranchCanBePublishedRule]],
        )->validate();

        $branch->forceFill([
            'is_active' => true,
            'is_visible_on_site' => true,
        ])->save();

        return $branch->refresh();
    }
}
