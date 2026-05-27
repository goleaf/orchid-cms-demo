<?php

namespace App\Http\Controllers;

use App\Actions\GetKnowledgeArticleAction;
use App\Models\KnowledgeArticle;
use Illuminate\Contracts\View\View;

class KnowledgeArticleController extends Controller
{
    public function __invoke(KnowledgeArticle $knowledgeArticle, GetKnowledgeArticleAction $article): View
    {
        return view('site.blog-show', $article->handle($knowledgeArticle));
    }
}
