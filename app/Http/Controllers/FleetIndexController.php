<?php

namespace App\Http\Controllers;

use App\Actions\GetFleetDirectoryAction;
use Illuminate\Contracts\View\View;

class FleetIndexController extends Controller
{
    public function __invoke(GetFleetDirectoryAction $fleet): View
    {
        return view('site.fleet', $fleet->handle());
    }
}
