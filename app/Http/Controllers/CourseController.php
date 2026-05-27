<?php

namespace App\Http\Controllers;

use App\Actions\GetCourseIndexPageAction;
use App\Actions\GetProgramCategoryPageAction;
use App\Models\TrainingProgram;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request, GetCourseIndexPageAction $page): View
    {
        return view('site.courses-index', $page->handle($request->query('category')));
    }

    public function show(TrainingProgram $trainingProgram, GetProgramCategoryPageAction $page): View
    {
        return view('site.category', $page->handle($trainingProgram));
    }
}
