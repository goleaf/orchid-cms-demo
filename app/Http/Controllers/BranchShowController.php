<?php

namespace App\Http\Controllers;

use App\Actions\GetBranchPageAction;
use App\Models\Branch;
use Illuminate\Contracts\View\View;

class BranchShowController extends Controller
{
    public function __invoke(Branch $branch, GetBranchPageAction $page): View
    {
        return view('site.branch', $page->handle($branch));
    }
}
