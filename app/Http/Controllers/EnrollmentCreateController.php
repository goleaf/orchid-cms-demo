<?php

namespace App\Http\Controllers;

use App\Actions\GetEnrollmentFormAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EnrollmentCreateController extends Controller
{
    public function __invoke(Request $request, GetEnrollmentFormAction $form): View
    {
        return view('site.apply', $form->handle([
            ...$request->only([
                'source',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'program',
                'instructor',
            ]),
            'referrer_url' => url()->previous(),
        ]));
    }
}
