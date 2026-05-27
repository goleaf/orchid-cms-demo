<?php

namespace App\Http\Controllers;

use App\Actions\GetBranchIndexPageAction;
use App\Actions\GetBranchPageAction;
use App\Models\Branch;
use Illuminate\Contracts\View\View;

class BranchController extends Controller
{
    public function index(GetBranchIndexPageAction $page): View
    {
        return view('site.branches-index', $page->handle());
    }

    public function show(Branch $branch, GetBranchPageAction $page): View
    {
        return view('site.branch', $page->handle($branch));
    }
}
