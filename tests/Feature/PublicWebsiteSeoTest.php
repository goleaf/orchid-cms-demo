<?php

namespace Tests\Feature;

use App\Actions\GenerateSeoMetadataAction;
use App\Models\Branch;
use App\Models\Course;
use App\Models\SitePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_loads_and_includes_visible_public_urls(): void
    {
        $this->seed();

        $course = Course::query()
            ->where('slug', 'category-b-manual')
            ->firstOrFail();
        $branch = Branch::query()
            ->where('slug', 'vilnius-main')
            ->firstOrFail();

        $this->get(route('site.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('website.home'), false)
            ->assertSee(route('website.pricing'), false)
            ->assertSee(route('website.contacts'), false)
            ->assertSee(route('website.courses.show', $course), false)
            ->assertSee(route('website.branches.show', ['branch' => $branch->slug]), false);
    }

    public function test_sitemap_excludes_hidden_and_noindex_catalog_records(): void
    {
        $this->seed();

        $hiddenCourse = Course::factory()->create([
            'slug' => 'hidden-seo-course',
            'title' => 'Hidden SEO Course',
            'is_active' => true,
            'is_visible_on_site' => false,
            'is_indexable' => true,
        ]);
        $noindexCourse = Course::factory()->create([
            'slug' => 'noindex-seo-course',
            'title' => 'Noindex SEO Course',
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => false,
        ]);
        $hiddenBranch = Branch::factory()->create([
            'slug' => 'hidden-seo-branch',
            'is_active' => true,
            'is_visible_on_site' => false,
            'is_indexable' => true,
        ]);
        $noindexBranch = Branch::factory()->create([
            'slug' => 'noindex-seo-branch',
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => false,
        ]);

        $response = $this->get(route('site.sitemap'))
            ->assertOk();

        $response
            ->assertDontSee(route('website.courses.show', $hiddenCourse), false)
            ->assertDontSee(route('website.courses.show', $noindexCourse), false)
            ->assertDontSee(route('website.branches.show', ['branch' => $hiddenBranch->slug]), false)
            ->assertDontSee(route('website.branches.show', ['branch' => $noindexBranch->slug]), false);
    }

    public function test_noindex_site_pages_are_excluded_from_sitemap(): void
    {
        $this->seed();

        $indexablePage = SitePage::factory()
            ->published()
            ->active()
            ->indexable()
            ->create(['slug' => 'seo-indexable-page']);
        $noindexPage = SitePage::factory()
            ->published()
            ->active()
            ->noindex()
            ->create(['slug' => 'seo-noindex-page']);

        $this->get(route('site.sitemap'))
            ->assertOk()
            ->assertSee(route('website.pages.show', $indexablePage), false)
            ->assertDontSee(route('website.pages.show', $noindexPage), false);
    }

    public function test_robots_loads_and_disallows_admin_paths(): void
    {
        $this->seed();

        $this->get(route('site.robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *')
            ->assertSee('Allow: /')
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /platform')
            ->assertSee('Sitemap: '.route('site.sitemap'));
    }

    public function test_seo_fallback_and_open_graph_tags_work(): void
    {
        $this->seed();

        $course = Course::factory()->create([
            'slug' => 'fallback-seo-course',
            'title' => 'Fallback SEO Course',
            'title_translations' => [],
            'name_translations' => [],
            'short_description' => 'Fallback SEO course description.',
            'seo_title' => null,
            'seo_title_translations' => null,
            'meta_description' => null,
            'seo_description_translations' => null,
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
        ]);

        $metadata = app(GenerateSeoMetadataAction::class)->handle([
            'name_translations' => ['ru' => 'Fallback generated title'],
            'description_translations' => ['ru' => 'Fallback generated description'],
        ]);

        $this->assertSame('Fallback generated title', $metadata['seo_title_translations']['ru']);
        $this->assertSame('Fallback generated description', $metadata['seo_description_translations']['ru']);
        $this->assertSame('Fallback generated title', $metadata['og_title_translations']['ru']);

        $this->get(route('website.courses.show', $course))
            ->assertOk()
            ->assertSee('<title>Fallback SEO Course | '.tkey('website.brand.name').'</title>', false)
            ->assertSee('<link rel="canonical" href="'.route('website.courses.show', $course).'">', false)
            ->assertSee('<meta property="og:url" content="'.route('website.courses.show', $course).'">', false)
            ->assertSee('<meta property="og:type" content="website">', false);
    }
}
