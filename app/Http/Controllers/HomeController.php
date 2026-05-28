<?php

namespace App\Http\Controllers;

use App\Actions\GetHomePageAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, GetHomePageAction $homePage): View
    {
        return view('site.home', $homePage->handle($request));
    }
}
