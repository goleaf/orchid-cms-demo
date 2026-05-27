<?php

namespace App\Http\Controllers;

use App\Actions\GetHomePageAction;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(GetHomePageAction $homePage): View
    {
        return view('site.home', $homePage->handle());
    }
}
