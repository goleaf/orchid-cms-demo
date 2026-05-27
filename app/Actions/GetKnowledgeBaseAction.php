<?php

namespace App\Actions;

use App\Models\KnowledgeArticle;

class GetKnowledgeBaseAction
{
    private const CATEGORY_LABEL_KEYS = [
        'selection' => 'website.blog.categories.selection',
        'learning' => 'website.blog.categories.learning',
        'exam' => 'website.blog.categories.exam',
        'theory' => 'website.blog.categories.theory',
        'practice' => 'website.blog.categories.practice',
        'rules' => 'website.blog.categories.rules',
        'student' => 'website.blog.categories.student',
        'system_design_overview' => 'website.blog.categories.system_design_overview',
        'system_design_getting_started' => 'website.blog.categories.system_design_getting_started',
        'system_design_foundation' => 'website.blog.categories.system_design_foundation',
        'system_design_core_concepts' => 'website.blog.categories.system_design_core_concepts',
        'system_design_building_blocks' => 'website.blog.categories.system_design_building_blocks',
        'system_design_data_layer' => 'website.blog.categories.system_design_data_layer',
        'system_design_distributed_systems' => 'website.blog.categories.system_design_distributed_systems',
        'system_design_architecture_patterns' => 'website.blog.categories.system_design_architecture_patterns',
        'system_design_real_world_designs' => 'website.blog.categories.system_design_real_world_designs',
        'system_design_senior_engineer_thinking' => 'website.blog.categories.system_design_senior_engineer_thinking',
        'system_design_vibecoding_masterclass' => 'website.blog.categories.system_design_vibecoding_masterclass',
        'system_design_assets' => 'website.blog.categories.system_design_assets',
        'system_design_community' => 'website.blog.categories.system_design_community',
        'system_design_resources' => 'website.blog.categories.system_design_resources',
    ];

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $articles = KnowledgeArticle::query()
            ->forPublicList()
            ->published()
            ->orderByDesc('published_at')
            ->simplePaginate(9)
            ->withQueryString();

        $articles->getCollection()->each(function (KnowledgeArticle $article): void {
            $article->setAttribute('category_label', self::categoryLabel($article->category));
        });

        return [
            'articles' => $articles,
            'categories' => self::categoryLabels(),
            'seoTitle' => tkey('website.blog.seo.title'),
            'seoDescription' => tkey('website.blog.seo.description'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return collect(self::CATEGORY_LABEL_KEYS)
            ->map(fn (string $key): string => tkey($key))
            ->all();
    }

    public static function categoryLabel(string $category): string
    {
        $key = self::CATEGORY_LABEL_KEYS[$category] ?? null;

        return $key === null ? $category : tkey($key);
    }
}
