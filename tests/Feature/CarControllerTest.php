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

    protected function getValidCarData(): array
    {
        return [
            'model' => 'New Test Car',
            'type' => 'SEDAN',
            'seats' => 5,
            'fuel_type' => 'GASOLINE',
            'engine_capacity' => 2000,
            'year' => 2024,
            'transmission' => 'AUTOMATIC',
            'description' => 'Test car description',
            'daily_price' => 100,
            'main_image' => \Illuminate\Http\UploadedFile::fake()->image('car.jpg', 400, 300),
        ];
    }

    #[Test]
    public function admin_can_store_new_car(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('cars.store'), $this->getValidCarData());

        $response->assertRedirect();

        $this->assertDatabaseHas('cars', [
            'model' => 'New Test Car',
            'type' => 'SEDAN',
            'year' => 2024,
        ]);
    }

    #[Test]
    public function guest_cannot_store_car(): void
    {
        $response = $this->post(route('cars.store'), $this->getValidCarData());

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('cars', ['model' => 'New Test Car']);
    }

    #[Test]
    public function admin_can_update_car(): void
    {
        $car = Car::factory()->create([
            'model' => 'Old Model',
            'type' => 'SEDAN',
        ]);

        $updateData = $this->getValidCarData();
        $updateData['model'] = 'Updated Model';
        $updateData['type'] = 'SUV';
        $updateData['seats'] = 7;
        unset($updateData['main_image']);

        $response = $this->actingAs($this->admin)
            ->put(route('cars.update', $car), $updateData);

        $response->assertRedirect(route('cars.show', $car));

        $car->refresh();
        $this->assertEquals('Updated Model', $car->model);
        $this->assertEquals('SUV', $car->type);
    }

    #[Test]
    public function guest_cannot_update_car(): void
    {
        $car = Car::factory()->create(['model' => 'Original Model']);

        $response = $this->put(route('cars.update', $car), $this->getValidCarData());

        $response->assertRedirect(route('login'));

        $car->refresh();
        $this->assertEquals('Original Model', $car->model);
    }

    #[Test]
    public function admin_can_delete_car(): void
    {
        $car = Car::factory()->create();
        $carId = $car->id;

        $response = $this->actingAs($this->admin)
            ->delete(route('cars.destroy', $car));

        $response->assertRedirect(route('cars.index'));

        $this->assertDatabaseMissing('cars', ['id' => $carId]);
    }

    #[Test]
    public function guest_cannot_delete_car(): void
    {
        $car = Car::factory()->create();

        $response = $this->delete(route('cars.destroy', $car));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('cars', ['id' => $car->id]);
    }

    #[Test]
    public function admin_can_toggle_car_visibility(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $response = $this->actingAs($this->admin)
            ->patch(route('cars.toggle-visibility', $car));

        $response->assertRedirect();

        $car->refresh();
        $this->assertTrue($car->hidden);

        $this->actingAs($this->admin)
            ->patch(route('cars.toggle-visibility', $car));

        $car->refresh();
        $this->assertFalse($car->hidden);
    }

    #[Test]
    public function guest_cannot_toggle_car_visibility(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $response = $this->patch(route('cars.toggle-visibility', $car));

        $response->assertRedirect(route('login'));

        $car->refresh();
        $this->assertFalse($car->hidden);
    }

    #[Test]
    public function store_car_fails_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('cars.create'))
            ->post(route('cars.store'), []);

        $response->assertRedirect(route('cars.create'));
        $response->assertSessionHasErrors([
            'model',
            'type',
            'seats',
            'fuel_type',
            'year',
            'transmission',
        ]);
    }

    #[Test]
    public function store_car_fails_with_invalid_type(): void
    {
        $data = $this->getValidCarData();
        $data['type'] = 'InvalidType';

        $response = $this->actingAs($this->admin)
            ->from(route('cars.create'))
            ->post(route('cars.store'), $data);

        $response->assertRedirect(route('cars.create'));
        $response->assertSessionHasErrors(['type']);
    }

    #[Test]
    public function store_car_fails_with_invalid_year(): void
    {
        $data = $this->getValidCarData();
        $data['year'] = 1800;

        $response = $this->actingAs($this->admin)
            ->from(route('cars.create'))
            ->post(route('cars.store'), $data);

        $response->assertRedirect(route('cars.create'));
        $response->assertSessionHasErrors(['year']);
    }

    #[Test]
    public function non_admin_user_cannot_access_car_crud(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $car = Car::factory()->create();

        $this->actingAs($user)->get(route('cars.create'))->assertStatus(403);
        $this->actingAs($user)->get(route('cars.edit', $car))->assertStatus(403);
        $this->actingAs($user)
            ->post(route('cars.store'), $this->getValidCarData())
            ->assertStatus(403);
        $this->actingAs($user)
            ->put(route('cars.update', $car), $this->getValidCarData())
            ->assertStatus(403);
        $this->actingAs($user)
            ->delete(route('cars.destroy', $car))
            ->assertStatus(403);
    }
}
