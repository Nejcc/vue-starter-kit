<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AboutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders(): void
    {
        $response = $this->get(route('about'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('About')
        );
    }
}
