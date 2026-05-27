<?php

namespace App\Http\Controllers;

use App\Actions\CaptureUtmDataAction;
use App\Actions\GetEnrollmentFormAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EnrollmentCreateController extends Controller
{
    public function __invoke(Request $request, GetEnrollmentFormAction $form, CaptureUtmDataAction $tracking): View
    {
        return view('site.apply', $form->handle([
            ...$tracking->handle($request),
            ...$request->only([
                'program',
                'branch',
                'group',
                'instructor',
            ]),
            'form_name' => 'enrollment',
            'form_page' => $request->fullUrl(),
        ]));
    }
}
