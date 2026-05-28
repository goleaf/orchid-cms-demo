<?php

namespace App\Http\Controllers;

use App\Actions\GetProgramCategoryPageAction;
use App\Models\TrainingProgram;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProgramCategoryController extends Controller
{
    public function __invoke(Request $request, TrainingProgram $trainingProgram, GetProgramCategoryPageAction $category): View
    {
        return view('site.category', $category->handle($trainingProgram, $request));
    }
}
