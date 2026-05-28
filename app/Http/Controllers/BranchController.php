<?php

namespace App\Http\Controllers;

use App\Actions\GetBranchIndexPageAction;
use App\Actions\GetBranchPageAction;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request, GetBranchIndexPageAction $page): View
    {
        return view('site.branches-index', $page->handle($request));
    }

    public function show(Branch $branch, GetBranchPageAction $page): View
    {
        return view('site.branch', $page->handle($branch));
    }
}
