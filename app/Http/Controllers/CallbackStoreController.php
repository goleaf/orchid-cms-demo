<?php

namespace App\Http\Controllers;

use App\Actions\CreateCallbackLeadAction;
use App\Http\Requests\StoreCallbackLeadRequest;
use App\Support\Site\SiteTracking;
use Illuminate\Http\RedirectResponse;

class CallbackStoreController extends Controller
{
    public function __invoke(StoreCallbackLeadRequest $request, CreateCallbackLeadAction $createLead): RedirectResponse
    {
        $createLead->handle([
            ...$request->validated(),
            ...SiteTracking::payload($request, [
                'source' => $request->input('source', 'callback'),
                'form_name' => $request->input('form_name', 'callback'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('site.contacts')),
            ]),
        ]);

        return redirect()
            ->route('site.thanks')
            ->with('status', tkey('website.messages.callback_received'));
    }
}
