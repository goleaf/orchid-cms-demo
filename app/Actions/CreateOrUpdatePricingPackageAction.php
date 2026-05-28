<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Models\PricingPackage;

class CreateOrUpdatePricingPackageAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?PricingPackage $package, array $attributes): PricingPackage
    {
        $package ??= new PricingPackage;
        $attributes = $this->assignSortablePosition($package, $attributes);

        $package->fill($attributes);
        $package->save();

        return $package->refresh();
    }
}
