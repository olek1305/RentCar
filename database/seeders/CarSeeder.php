<?php

namespace Database\Seeders;

use App\Models\Car;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run(): void
    {
        $types = CAR::TYPES;
        $fuelTypes = CAR::fuelTypes;
        $transmissions = CAR::transmissions;

        for ($i = 0; $i < 30; $i++) {
            $model = $this->generateModelName();
            $type = $this->faker->randomElement($types);
            $seats = $this->generateSeats($type);
            $fuelType = $this->faker->randomElement($fuelTypes);

            $carData = [
                'model' => $model,
                'type' => $type,
                'seats' => $seats,
                'fuel_type' => $fuelType,
                'engine_capacity' => $this->generateEngineCapacity($fuelType),
                'year' => $this->faker->numberBetween(2010, 2023),
                'transmission' => $this->faker->randomElement($transmissions),
                'rental_prices' => $this->generateRentalPrices(),
                'description' => $this->faker->paragraph(3),
            ];

            $this->createCarWithImages($carData);
        }
    }

    private function generateModelName(): string
    {
        $letters = $this->faker->randomLetter().$this->faker->randomLetter();
        $numbers = $this->faker->numberBetween(1, 9).$this->faker->numberBetween(0, 9);

        return strtoupper($letters).' '.$numbers;
    }

    private function generateSeats(string $type): int
    {
        return match ($type) {
            'Sedan', 'Coupe' => 4,
            'Convertible' => 2,
            'SUV', 'Combi' => $this->faker->randomElement([5, 7]),
            default => 5
        };
    }

    private function generateEngineCapacity(string $fuelType): int
    {
        if ($fuelType === 'Electric') {
            return 0;
        }
        if ($fuelType === 'Hybrid') {
            return $this->faker->numberBetween(1000, 2000);
        }

        return $this->faker->numberBetween(1000, 3500);
    }

    private function generateRentalPrices(): array
    {
        $basePrice = $this->faker->numberBetween(40, 100);

        return [
            '1-2' => $basePrice,
            '3-6' => $basePrice * 0.9,
            '7+' => $basePrice * 0.8,
        ];
    }

    private function createCarWithImages(array $carData): void
    {
        $modelSlug = Str::slug($carData['model']);

        try {
            $mainImageUrl = "https://placehold.co/400x300.jpg?text={$modelSlug}";
            $carData['main_image'] = $this->downloadAndSaveImage($mainImageUrl, 'cars/main');

            $galleryImages = [];
            for ($i = 1; $i <= 3; $i++) {
                $galleryUrl = "https://placehold.co/600x400.jpg?text={$modelSlug}+{$i}";
                $galleryImages[] = $this->downloadAndSaveImage($galleryUrl, 'cars/gallery');
            }
            $carData['gallery_images'] = json_encode($galleryImages);
            $carData['rental_prices'] = json_encode($carData['rental_prices']);

            Car::create($carData);
        } catch (Exception $e) {
            $this->command->error("Failed to create car {$carData['model']}: ".$e->getMessage());
        }
    }

    private function downloadAndSaveImage(string $url, string $directory): string
    {
        $response = Http::get($url);

        if (! $response->successful()) {
            throw new Exception("Failed to download image from {$url}");
        }

        $filename = Str::uuid().'.jpg';
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $response->body());

        return $path;
    }
}
