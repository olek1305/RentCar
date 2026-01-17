<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function home_page_is_accessible(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    #[Test]
    public function condition_page_is_accessible(): void
    {
        $response = $this->get(route('condition'));

        $response->assertStatus(200);
        $response->assertViewIs('condition');
    }

    #[Test]
    public function contact_page_is_accessible(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertViewIs('contact');
    }
}
