<?php

namespace App\Http\Controllers;

use App\Actions\GetContactPageAction;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(GetContactPageAction $page): View
    {
        return view('site.contacts', $page->handle());
    }
}
