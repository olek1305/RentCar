<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_accessible(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[Test]
    public function login_page_contains_form(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('email');
        $response->assertSee('password');
    }

    #[Test]
    public function authenticated_admin_can_access_admin_index(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function guest_is_redirected_to_login_from_admin(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('login'));
    }
}
