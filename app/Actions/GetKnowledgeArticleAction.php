<?php

namespace App\Actions;

use App\Models\KnowledgeArticle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetKnowledgeArticleAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(KnowledgeArticle $article): array
    {
        $published = KnowledgeArticle::query()
            ->forPublicDetail()
            ->published()
            ->whereKey($article->id)
            ->first();

        if ($published === null) {
            throw new NotFoundHttpException;
        }

        return [
            'article' => $published,
            'seoTitle' => $published->seo_title ?: $published->title,
            'seoDescription' => $published->meta_description ?: $published->excerpt,
            'canonical' => $published->canonical_url,
            'ogImage' => $published->open_graph_image,
        ];
    }
}
