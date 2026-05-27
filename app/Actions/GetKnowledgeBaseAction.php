<?php

namespace App\Actions;

use App\Models\KnowledgeArticle;

class GetKnowledgeBaseAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'articles' => KnowledgeArticle::query()
                ->forPublicList()
                ->published()
                ->orderByDesc('published_at')
                ->simplePaginate(9)
                ->withQueryString(),
            'categories' => [
                'selection' => 'How to choose a driving school',
                'learning' => 'How training works',
                'exam' => 'Exam preparation',
                'theory' => 'Theory advice',
                'practice' => 'Practice advice',
                'rules' => 'Rule changes',
                'student' => 'Student instructions',
            ],
            'seoTitle' => 'Driving school knowledge base | DrivePro Academy',
            'seoDescription' => 'Articles about choosing a driving school, theory, practice, exam preparation, mistakes, news, and student instructions.',
        ];
    }
}
