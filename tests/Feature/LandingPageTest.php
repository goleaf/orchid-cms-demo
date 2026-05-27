<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_published_content(): void
    {
        LandingPage::factory()
            ->home()
            ->published()
            ->create([
                'hero_title' => 'Editable driving school platform',
                'about_heading' => 'Prepared content flow',
            ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Editable driving school platform')
            ->assertSee('Prepared content flow');
    }

    public function test_homepage_does_not_render_unpublished_content(): void
    {
        LandingPage::factory()
            ->home()
            ->create([
                'hero_title' => 'Draft homepage',
                'published_at' => null,
            ]);

        $this->get('/')->assertNotFound();
    }
}
