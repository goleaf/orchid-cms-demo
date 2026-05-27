<?php

namespace App\Actions;

use App\Models\PricingPackage;

class CreateOrUpdatePricingPackageAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?PricingPackage $package, array $attributes): PricingPackage
    {
        $package ??= new PricingPackage;
        $package->fill($attributes);
        $package->save();

        return $package->refresh();
    }
}
