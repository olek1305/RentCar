<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    public function definition(): array
    {
        $types = ['Sedan', 'Coupe', 'Convertible', 'SUV', 'Combi', 'ELECTRIC'];
        $fuelTypes = ['Gasoline', 'Diesel', 'Hybrid', 'Electric'];
        $transmissions = ['Automatic', 'Manual'];

        $model = strtoupper($this->faker->randomLetter().$this->faker->randomLetter()).' '.$this->faker->numberBetween(10, 99);
        $type = $this->faker->randomElement($types);

        return [
            'model' => $model,
            'type' => $type,
            'seats' => $this->generateSeats($type),
            'fuel_type' => $this->faker->randomElement($fuelTypes),
            'engine_capacity' => $this->faker->numberBetween(1000, 3500),
            'year' => $this->faker->numberBetween(2010, 2023),
            'transmission' => $this->faker->randomElement($transmissions),
            'rental_prices' => $this->generateRentalPrices(),
            'description' => $this->faker->paragraph(3),
            'main_image' => 'cars/main/'.$this->faker->uuid().'.jpg',
            'gallery_images' => json_encode([
                'cars/gallery/'.$this->faker->uuid().'.jpg',
                'cars/gallery/'.$this->faker->uuid().'.jpg',
            ]),
            'hidden' => $this->faker->boolean(20), // 20% chance of being hidden
        ];
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

    private function generateRentalPrices(): string
    {
        $basePrice = $this->faker->numberBetween(40, 100);

        return json_encode([
            '1-2' => $basePrice,
            '3-6' => $basePrice * 0.9,
            '7+' => $basePrice * 0.8,
        ]);
    }
}
