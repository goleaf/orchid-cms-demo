<?php

namespace App\Http\Controllers;

use App\Actions\CreateCallbackLeadAction;
use App\Http\Requests\StoreCallbackLeadRequest;
use Illuminate\Http\RedirectResponse;

class CallbackStoreController extends Controller
{
    public function __invoke(StoreCallbackLeadRequest $request, CreateCallbackLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                'source' => $request->input('source', 'callback'),
                'form_name' => $request->input('form_name', 'callback'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('site.contacts')),
            ],
            $request,
        );

        return redirect()
            ->route('site.thanks')
            ->with('status', tkey('website.forms.messages.callback_success'));
    }
}
