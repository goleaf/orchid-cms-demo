<?php

namespace App\Http\Controllers;

use App\Actions\CreateEnrollmentLeadAction;
use App\Http\Requests\StoreEnrollmentLeadRequest;
use Illuminate\Http\RedirectResponse;

class EnrollmentStoreController extends Controller
{
    public function __invoke(StoreEnrollmentLeadRequest $request, CreateEnrollmentLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            $request->validated(),
            $request->file('documents', []),
        );

        return redirect()
            ->route('site.apply')
            ->with('status', 'Application received. A manager will contact you soon.');
    }
}
