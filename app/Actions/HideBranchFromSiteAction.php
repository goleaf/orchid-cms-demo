<?php

namespace App\Actions;

use App\Models\Branch;

class HideBranchFromSiteAction
{
    public function handle(Branch $branch): Branch
    {
        $branch->forceFill(['is_visible_on_site' => false])->save();

        return $branch->refresh();
    }
}
