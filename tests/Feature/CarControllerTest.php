<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->admin()->create();
    }

    #[Test]
    public function cars_index_page_is_accessible(): void
    {
        Car::factory()->count(3)->create(['hidden' => false]);

        $response = $this->get(route('cars.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cars.index');
    }

    #[Test]
    public function cars_index_shows_only_visible_cars_for_guests(): void
    {
        Car::factory()->create(['hidden' => false, 'model' => 'Visible Car']);
        Car::factory()->create(['hidden' => true, 'model' => 'Hidden Car']);

        $response = $this->get(route('cars.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Car');
        $response->assertDontSee('Hidden Car');
    }

    #[Test]
    public function cars_index_shows_all_cars_for_admin(): void
    {
        Car::factory()->create(['hidden' => false, 'model' => 'Visible Car']);
        Car::factory()->create(['hidden' => true, 'model' => 'Hidden Car']);

        $response = $this->actingAs($this->admin)->get(route('cars.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Car');
        $response->assertSee('Hidden Car');
    }

    #[Test]
    public function car_show_page_displays_visible_car(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $response = $this->get(route('cars.show', $car));

        $response->assertStatus(200);
        $response->assertViewIs('cars.show');
        $response->assertSee($car->model);
    }

    #[Test]
    public function car_show_page_returns_404_for_hidden_car_guest(): void
    {
        $car = Car::factory()->create(['hidden' => true]);

        $response = $this->get(route('cars.show', $car));

        $response->assertStatus(404);
    }

    #[Test]
    public function admin_can_view_hidden_car(): void
    {
        $car = Car::factory()->create(['hidden' => true]);

        $response = $this->actingAs($this->admin)->get(route('cars.show', $car));

        $response->assertStatus(200);
        $response->assertSee($car->model);
    }

    #[Test]
    public function guest_cannot_access_create_page(): void
    {
        $response = $this->get(route('cars.create'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_access_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cars.create'));

        $response->assertStatus(200);
        $response->assertViewIs('cars.create');
    }

    #[Test]
    public function guest_cannot_access_edit_page(): void
    {
        $car = Car::factory()->create();

        $response = $this->get(route('cars.edit', $car));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_access_edit_page(): void
    {
        $car = Car::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('cars.edit', $car));

        $response->assertStatus(200);
        $response->assertViewIs('cars.edit');
    }

    #[Test]
    public function cars_index_is_paginated(): void
    {
        Car::factory()->count(15)->create(['hidden' => false]);

        $response = $this->get(route('cars.index'));

        $response->assertStatus(200);
        $response->assertViewHas('cars');
    }

    #[Test]
    public function cars_index_handles_page_parameter(): void
    {
        Car::factory()->count(25)->create(['hidden' => false]);

        $response = $this->get(route('cars.index', ['page' => 2]));

        $response->assertStatus(200);
    }

    #[Test]
    public function car_show_displays_car_details(): void
    {
        $car = Car::factory()->create([
            'hidden' => false,
            'model' => 'Test Model',
            'type' => 'Sedan',
            'year' => 2023,
        ]);

        $response = $this->get(route('cars.show', $car));

        $response->assertStatus(200);
        $response->assertSee('Test Model');
        $response->assertSee('2023');
    }

    #[Test]
    public function car_create_page_shows_form_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('cars.create'));

        $response->assertStatus(200);
        $response->assertSee('model');
        $response->assertSee('type');
    }

    #[Test]
    public function car_edit_page_shows_existing_data(): void
    {
        $car = Car::factory()->create([
            'model' => 'Existing Model',
            'type' => 'SUV',
        ]);

        $response = $this->actingAs($this->admin)->get(route('cars.edit', $car));

        $response->assertStatus(200);
        $response->assertSee('Existing Model');
    }
}
