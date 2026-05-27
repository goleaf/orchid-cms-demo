<?php

namespace App\Http\Controllers;

use App\Actions\CreateWebsiteLeadAction;
use App\Http\Requests\StoreWebsiteLeadRequest;
use Illuminate\Http\RedirectResponse;

class EnrollmentStoreController extends Controller
{
    public function __invoke(StoreWebsiteLeadRequest $request, CreateWebsiteLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                'source' => $request->input('source', 'website'),
                'form_name' => $request->input('form_name', 'enrollment'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('site.apply')),
            ],
            $request,
            $request->file('documents', []),
        );

        return redirect()
            ->route('site.thanks')
            ->with('status', tkey('website.messages.application_received'));
    }
}
