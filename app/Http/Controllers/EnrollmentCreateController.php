<?php

namespace App\Http\Controllers;

use App\Actions\GetEnrollmentFormAction;
use App\Support\Site\SiteTracking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EnrollmentCreateController extends Controller
{
    public function __invoke(Request $request, GetEnrollmentFormAction $form): View
    {
        return view('site.apply', $form->handle([
            ...SiteTracking::payload($request),
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
