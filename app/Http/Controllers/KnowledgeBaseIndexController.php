<?php

namespace App\Http\Controllers;

use App\Actions\GetKnowledgeBaseAction;
use Illuminate\Contracts\View\View;

class KnowledgeBaseIndexController extends Controller
{
    public function __invoke(GetKnowledgeBaseAction $knowledgeBase): View
    {
        return view('site.blog-index', $knowledgeBase->handle());
    }
}
