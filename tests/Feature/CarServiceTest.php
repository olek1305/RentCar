<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use App\Services\CarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CarService $carService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carService = new CarService;
    }

    #[Test]
    public function it_returns_only_visible_cars_for_guests()
    {
        // Ensure no user is authenticated
        auth()->logout();

        // Clear and create test data
        Car::query()->delete();
        Car::factory()->create(['hidden' => false]);
        Car::factory()->count(2)->create(['hidden' => true]);

        // Get results
        $cars = $this->carService->getVisibleCars()->get();

        // Assertions
        $this->assertCount(1, $cars);

        // Either:
        $this->assertEquals(0, $cars->first()->hidden);
    }

    #[Test]
    public function it_respects_include_hidden_for_admins()
    {
        // Create test cars
        Car::factory()->create(['hidden' => false]);
        Car::factory()->create(['hidden' => true]);

        // Authenticate admin
        $this->actingAs(User::factory()->create());

        // Case 1: Don't include hidden
        $visibleOnly = $this->carService->getVisibleCars()->get();
        $this->assertCount(1, $visibleOnly);

        // Case 2: Include hidden
        $allCars = $this->carService->getVisibleCars(true)->get();
        $this->assertCount(2, $allCars);
    }

    #[Test]
    public function it_creates_car_with_main_and_gallery_images()
    {
        Storage::fake('public');

        $mainImage = UploadedFile::fake()->image('main.jpg');
        $galleryImages = [
            UploadedFile::fake()->image('gallery1.jpg'),
            UploadedFile::fake()->image('gallery2.jpg'),
        ];

        $carData = [
            'model' => 'Tesla Model S',
            'type' => 'ELECTRIC',
            'seats' => 5,
            'fuel_type' => 'Electric',
            'engine_capacity' => 2000,
            'year' => '2023',
            'transmission' => 'Automatic',
            'daily_price' => 300,
            'description' => 'Test description',
        ];

        $car = $this->carService->createCar($carData, $mainImage, $galleryImages);

        $this->assertDatabaseHas('cars', ['model' => 'Tesla Model S']);
        Storage::disk('public')->assertExists($car->main_image);

        foreach ($car->gallery_images as $image) {
            Storage::disk('public')->assertExists($image);
        }
    }

    #[Test]
    public function it_updates_car_with_new_main_image_and_deletes_old_one()
    {
        Storage::fake('public');

        $car = Car::factory()->create([
            'main_image' => 'cars/main/old.jpg',
        ]);

        Storage::disk('public')->put('cars/main/old.jpg', 'dummy');

        $newImage = UploadedFile::fake()->image('new.jpg');

        $this->carService->updateCar(
            $car,
            [
                'model' => 'Updated Model', 'daily_price' => 100,
            ],
            $newImage,
            null,
            null,
            false,
            false,
            null
        );

        Storage::disk('public')->assertMissing('cars/main/old.jpg');
        Storage::disk('public')->assertExists($car->fresh()->main_image);
        $this->assertEquals('Updated Model', $car->fresh()->model);
    }
}
