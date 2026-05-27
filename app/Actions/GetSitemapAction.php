<?php

namespace App\Actions;

use App\Models\KnowledgeArticle;
use App\Models\TrainingProgram;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetSitemapAction
{
    /**
     * @return Collection<int, array{url: string, updated_at: Carbon|null}>
     */
    public function handle(): Collection
    {
        $staticUrls = collect([
            ['url' => route('site.home'), 'updated_at' => now()],
            ['url' => route('site.apply'), 'updated_at' => now()],
            ['url' => route('site.prices'), 'updated_at' => now()],
            ['url' => route('site.instructors'), 'updated_at' => now()],
            ['url' => route('site.fleet'), 'updated_at' => now()],
            ['url' => route('site.reviews'), 'updated_at' => now()],
            ['url' => route('site.blog.index'), 'updated_at' => now()],
            ['url' => route('site.contacts'), 'updated_at' => now()],
        ]);

        $programUrls = TrainingProgram::query()
            ->forAcademyList()
            ->active()
            ->orderBy('slug')
            ->get()
            ->map(fn (TrainingProgram $program): array => [
                'url' => route('site.courses.show', $program),
                'updated_at' => $program->updated_at,
            ]);

        $articleUrls = KnowledgeArticle::query()
            ->forPublicList()
            ->published()
            ->orderBy('slug')
            ->get()
            ->map(fn (KnowledgeArticle $article): array => [
                'url' => route('site.blog.show', $article),
                'updated_at' => $article->published_at,
            ]);

        return $staticUrls
            ->merge($programUrls)
            ->merge($articleUrls)
            ->values();
    }
}
