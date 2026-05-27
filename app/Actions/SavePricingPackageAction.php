<?php

namespace App\Actions;

use App\Models\PricingPackage;

class SavePricingPackageAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(PricingPackage $package, array $attributes): PricingPackage
    {
        return app(CreateOrUpdatePricingPackageAction::class)->handle($package, $attributes);
    }
}
