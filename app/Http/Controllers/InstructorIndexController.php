<?php

namespace App\Http\Controllers;

use App\Actions\GetInstructorDirectoryAction;
use Illuminate\Contracts\View\View;

class InstructorIndexController extends Controller
{
    public function __invoke(GetInstructorDirectoryAction $directory): View
    {
        return view('site.instructors', $directory->handle());
    }
}
