<?php

namespace App\Http\Controllers;

use App\Actions\GetProgramCategoryPageAction;
use App\Models\TrainingProgram;
use Illuminate\Contracts\View\View;

class ProgramCategoryController extends Controller
{
    public function __invoke(TrainingProgram $trainingProgram, GetProgramCategoryPageAction $category): View
    {
        return view('site.category', $category->handle($trainingProgram));
    }
}
