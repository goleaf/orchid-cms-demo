<?php

namespace App\Http\Controllers;

use App\Actions\CreateEnrollmentLeadAction;
use App\Http\Requests\StoreEnrollmentLeadRequest;
use App\Support\Site\SiteTracking;
use Illuminate\Http\RedirectResponse;

class EnrollmentStoreController extends Controller
{
    public function __invoke(StoreEnrollmentLeadRequest $request, CreateEnrollmentLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                ...SiteTracking::payload($request, [
                    'source' => $request->input('source', 'website'),
                    'form_name' => $request->input('form_name', 'enrollment'),
                    'form_page' => $request->input('form_page', url()->previous() ?: route('site.apply')),
                ]),
            ],
            $request->file('documents', []),
        );

        return redirect()
            ->route('site.thanks')
            ->with('status', tkey('website.messages.application_received'));
    }
}
