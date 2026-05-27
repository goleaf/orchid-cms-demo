<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemDesignVibecodingSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDesignVibecodingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_design_vibecoding_markdown_is_seeded_to_knowledge_base(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class, SystemDesignVibecodingSeeder::class]);

        $expectedCount = SystemDesignVibecodingSeeder::sourceMarkdownCount();

        $this->assertGreaterThan(100, $expectedCount);
        $this->assertSame(
            $expectedCount,
            KnowledgeArticle::query()
                ->where('slug', 'like', 'system-design-vibecoding-%')
                ->count(),
        );

        $article = KnowledgeArticle::query()
            ->where('slug', SystemDesignVibecodingSeeder::slugForSourcePath('01-foundation/01-client-server-model.md'))
            ->firstOrFail();

        $this->assertSame('Client-Server Model', $article->title);
        $this->assertSame('system_design_foundation', $article->category);
        $this->assertSame('system-design-vibecoding', $article->structured_data['source']);
    }

    public function test_imported_markdown_links_are_rewritten_to_public_blog_routes(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class, SystemDesignVibecodingSeeder::class]);

        $article = KnowledgeArticle::query()
            ->where('slug', SystemDesignVibecodingSeeder::slugForSourcePath('README.md'))
            ->firstOrFail();

        $this->assertStringContainsString(
            '/blog/'.SystemDesignVibecodingSeeder::slugForSourcePath('00-getting-started/README.md'),
            $article->body,
        );
        $this->assertStringNotContainsString('](00-getting-started/README.md)', $article->body);
    }

    public function test_imported_article_renders_through_public_knowledge_base_with_translated_labels(): void
    {
        $this->seed([LanguageSeeder::class, SystemTranslationSeeder::class, SystemDesignVibecodingSeeder::class]);

        $article = KnowledgeArticle::query()
            ->where('slug', SystemDesignVibecodingSeeder::slugForSourcePath('01-foundation/01-client-server-model.md'))
            ->firstOrFail();

        $this->get(route('site.blog.index'))
            ->assertOk()
            ->assertSee(tkey('website.blog.title'))
            ->assertSee(tkey('website.blog.actions.read'));

        $this->get(route('site.blog.show', $article))
            ->assertOk()
            ->assertSee('Client-Server Model')
            ->assertSee('What is the Client-Server Model?')
            ->assertSee(tkey('website.blog.categories.system_design_foundation'));
    }
}
