<?php

namespace App\Http\Controllers;

use App\Actions\GetPricingPageAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __invoke(Request $request, GetPricingPageAction $page): View
    {
        return view('site.prices', $page->handle($request));
    }
}
