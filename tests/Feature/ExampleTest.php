<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        LandingPage::factory()
            ->home()
            ->published()
            ->create();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
