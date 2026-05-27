<?php

namespace App\Http\Controllers;

use App\Actions\CreateCallbackLeadAction;
use App\Actions\CreateWebsiteLeadAction;
use App\Http\Requests\StoreCallbackLeadRequest;
use App\Http\Requests\StoreContactLeadRequest;
use App\Http\Requests\StoreWebsiteLeadRequest;
use Illuminate\Http\RedirectResponse;

class WebsiteLeadController extends Controller
{
    public function store(StoreWebsiteLeadRequest $request, CreateWebsiteLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                'source' => $request->input('source', 'website'),
                'form_name' => $request->input('form_name', 'website_application'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('website.home')),
            ],
            $request,
            $request->file('documents', []),
        );

        return redirect()
            ->route('website.thank_you')
            ->with('status', tkey('website.forms.messages.success'));
    }

    public function callback(StoreCallbackLeadRequest $request, CreateCallbackLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                'source' => $request->input('source', 'callback'),
                'form_name' => $request->input('form_name', 'callback'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('website.contacts')),
            ],
            $request,
        );

        return redirect()
            ->route('website.thank_you')
            ->with('status', tkey('website.forms.messages.callback_success'));
    }

    public function contact(StoreContactLeadRequest $request, CreateWebsiteLeadAction $createLead): RedirectResponse
    {
        $createLead->handle(
            [
                ...$request->validated(),
                'source' => $request->input('source', 'contact_form'),
                'form_name' => $request->input('form_name', 'contact'),
                'form_page' => $request->input('form_page', url()->previous() ?: route('website.contacts')),
            ],
            $request,
        );

        return redirect()
            ->route('website.thank_you')
            ->with('status', tkey('website.forms.messages.contact_success'));
    }
}
