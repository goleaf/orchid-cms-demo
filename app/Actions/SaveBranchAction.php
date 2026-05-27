<?php

namespace App\Actions;

use App\Models\Branch;

class SaveBranchAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Branch $branch, array $attributes): Branch
    {
        $branch->fill($attributes);
        $branch->save();

        return $branch;
    }
}
