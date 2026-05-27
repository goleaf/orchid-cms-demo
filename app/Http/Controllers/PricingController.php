<?php

namespace App\Http\Controllers;

use App\Actions\GetPricingPageAction;
use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function __invoke(GetPricingPageAction $page): View
    {
        return view('site.prices', $page->handle());
    }
}
