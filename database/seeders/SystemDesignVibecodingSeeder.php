<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\KnowledgeArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class SystemDesignVibecodingSeeder extends Seeder
{
    private const SOURCE_RELATIVE_PATH = 'resources/system-design-vibecoding';

    private const SOURCE_REPOSITORY = 'https://github.com/nimin1/system-design-vibecoding';

    private const SOURCE_COMMIT = '2601957c5b5227fb3b28140eb1cad9b55c39ad9e';

    private const CATEGORY_BY_ROOT = [
        '00-getting-started' => 'system_design_getting_started',
        '01-foundation' => 'system_design_foundation',
        '02-core-concepts' => 'system_design_core_concepts',
        '03-building-blocks' => 'system_design_building_blocks',
        '04-data-layer' => 'system_design_data_layer',
        '05-distributed-systems' => 'system_design_distributed_systems',
        '06-architecture-patterns' => 'system_design_architecture_patterns',
        '07-real-world-designs' => 'system_design_real_world_designs',
        '08-senior-engineer-thinking' => 'system_design_senior_engineer_thinking',
        '09-vibecoding-masterclass' => 'system_design_vibecoding_masterclass',
        'assets' => 'system_design_assets',
        'community' => 'system_design_community',
        'resources' => 'system_design_resources',
    ];

    public function run(): void
    {
        $sourcePath = base_path(self::SOURCE_RELATIVE_PATH);

        if (! File::isDirectory($sourcePath)) {
            return;
        }

        collect(File::allFiles($sourcePath))
            ->filter(fn (SplFileInfo $file): bool => Str::lower($file->getExtension()) === 'md')
            ->sortBy(fn (SplFileInfo $file): string => $this->sourceRelativePath($file))
            ->values()
            ->each(function (SplFileInfo $file, int $index): void {
                $relativePath = $this->sourceRelativePath($file);
                $markdown = File::get($file->getPathname());
                $title = $this->titleFromMarkdown($markdown, $relativePath);
                $excerpt = $this->excerptFromMarkdown($markdown);

                KnowledgeArticle::query()->updateOrCreate(
                    ['slug' => $this->articleSlug($relativePath)],
                    [
                        'title' => $title,
                        'category' => $this->categoryForPath($relativePath),
                        'excerpt' => $excerpt,
                        'body' => $this->rewriteMarkdownLinks($markdown, $relativePath),
                        'status' => ArticleStatus::Published,
                        'published_at' => now()->subDays(160 - min($index, 159)),
                        'seo_title' => $title.' | System Design',
                        'meta_description' => $excerpt,
                        'canonical_url' => null,
                        'open_graph_image' => null,
                        'structured_data' => [
                            'type' => 'Article',
                            'source' => 'system-design-vibecoding',
                            'source_repository' => self::SOURCE_REPOSITORY,
                            'source_commit' => self::SOURCE_COMMIT,
                            'source_path' => $relativePath,
                            'license' => 'MIT',
                        ],
                    ],
                );
            });
    }

    public static function sourceMarkdownCount(): int
    {
        $sourcePath = base_path(self::SOURCE_RELATIVE_PATH);

        if (! File::isDirectory($sourcePath)) {
            return 0;
        }

        return collect(File::allFiles($sourcePath))
            ->filter(fn (SplFileInfo $file): bool => Str::lower($file->getExtension()) === 'md')
            ->count();
    }

    public static function slugForSourcePath(string $relativePath): string
    {
        $withoutExtension = preg_replace('/\.md$/i', '', $relativePath) ?: $relativePath;
        $slug = Str::slug($withoutExtension);

        return 'system-design-vibecoding-'.($slug ?: md5($relativePath));
    }

    private function sourceRelativePath(SplFileInfo $file): string
    {
        return str_replace('\\', '/', $file->getRelativePathname());
    }

    private function articleSlug(string $relativePath): string
    {
        return self::slugForSourcePath($relativePath);
    }

    private function categoryForPath(string $relativePath): string
    {
        $root = Str::before($relativePath, '/');

        return self::CATEGORY_BY_ROOT[$root] ?? 'system_design_overview';
    }

    private function titleFromMarkdown(string $markdown, string $relativePath): string
    {
        foreach (preg_split('/\R/', $markdown) ?: [] as $line) {
            $line = trim((string) $line);

            if (preg_match('/^#\s+(.+)$/', $line, $matches) === 1) {
                return $this->plainText($matches[1]);
            }
        }

        return Str::headline(pathinfo($relativePath, PATHINFO_FILENAME));
    }

    private function excerptFromMarkdown(string $markdown): string
    {
        foreach (preg_split('/\R+/', $markdown) ?: [] as $line) {
            $line = trim((string) $line);

            if ($line === '' || Str::startsWith($line, ['#', '---', '```', '|'])) {
                continue;
            }

            $plainText = $this->plainText($line);

            if ($plainText !== '') {
                return Str::limit($plainText, 190);
            }
        }

        return 'System design learning material.';
    }

    private function plainText(string $value): string
    {
        $value = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $value) ?? $value;
        $value = preg_replace('/[*_`>#]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function rewriteMarkdownLinks(string $markdown, string $currentRelativePath): string
    {
        return preg_replace_callback(
            '/\]\(([^)\s]+\.md(?:#[^)]+)?)\)/i',
            function (array $matches) use ($currentRelativePath): string {
                $target = $matches[1];

                if (Str::startsWith($target, ['http://', 'https://', 'mailto:', '#'])) {
                    return ']('.$target.')';
                }

                [$targetPath, $anchor] = array_pad(explode('#', $target, 2), 2, null);
                $normalizedPath = $this->normalizeRelativePath(dirname($currentRelativePath).'/'.$targetPath);

                return '](/blog/'.$this->articleSlug($normalizedPath).($anchor ? '#'.$anchor : '').')';
            },
            $markdown,
        ) ?? $markdown;
    }

    private function normalizeRelativePath(string $path): string
    {
        $segments = [];

        foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }
}
