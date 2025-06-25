<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

/**
 * Run the database seeds.
 */
class CarSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            [
                'model' => 'Combi C3',
                'type' => 'Combi',
                'seats' => 5,
                'fuel_type' => 'Diesel',
                'engine_capacity' => 2000,
                'year' => 2019,
                'transmission' => 'Automată',
                'image' => 'https://placehold.co/400x300?text=Combi+1',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 70,
                    '3-7 days' => 60,
                    '8-22 days' => 50,
                    '23-45 days' => 45,
                    '46+ days' => 40,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            ],
            [
                'model' => 'Sedan C5',
                'type' => 'Sedan',
                'seats' => 4,
                'fuel_type' => 'Gas',
                'engine_capacity' => 1600,
                'year' => 2021,
                'transmission' => 'Manuală',
                'image' => 'https://placehold.co/400x300?text=Sedan+2',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 75,
                    '3-7 days' => 65,
                    '8-22 days' => 55,
                    '23-45 days' => 48,
                    '46+ days' => 42,
                ]),
                'description' => 'Elegancki sedan z komfortowym wnętrzem i nowoczesnymi technologiami, idealny na codzienne dojazdy i dłuższe wyjazdy.',
            ],
            [
                'model' => 'SUV V3',
                'type' => 'SUV',
                'seats' => 7,
                'fuel_type' => 'Diesel',
                'engine_capacity' => 2500,
                'year' => 2020,
                'transmission' => 'Automată',
                'image' => 'https://placehold.co/400x300?text=SUV+3',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 80,
                    '3-7 days' => 70,
                    '8-22 days' => 60,
                    '23-45 days' => 55,
                    '46+ days' => 50,
                ]),
                'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for lorem ipsum will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).',
            ],
            [
                'model' => 'Hatchback V32',
                'type' => 'Hatchback',
                'seats' => 5,
                'fuel_type' => 'Gas',
                'engine_capacity' => 1400,
                'year' => 2018,
                'transmission' => 'Manuală',
                'image' => 'https://placehold.co/400x300?text=Hatchback+4',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 50,
                    '3-7 days' => 45,
                    '8-22 days' => 40,
                    '23-45 days' => 38,
                    '46+ days' => 35,
                ]),
                'description' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old.',
            ],
            [
                'model' => 'Combi X75',
                'type' => 'Combi',
                'seats' => 5,
                'fuel_type' => 'Electric',
                'engine_capacity' => 0,
                'year' => 2022,
                'transmission' => 'Automată',
                'image' => 'https://placehold.co/400x300?text=Combi+5',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 90,
                    '3-7 days' => 85,
                    '8-22 days' => 75,
                    '23-45 days' => 70,
                    '46+ days' => 65,
                ]),
                'description' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old.',
            ],
            [
                'model' => 'SUV S35s',
                'type' => 'SUV',
                'seats' => 5,
                'fuel_type' => 'Hybrid',
                'engine_capacity' => 1800,
                'year' => 2023,
                'transmission' => 'Automată',
                'image' => 'https://placehold.co/400x300?text=SUV+6',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 85,
                    '3-7 days' => 75,
                    '8-22 days' => 65,
                    '23-45 days' => 60,
                    '46+ days' => 55,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ],
            [
                'model' => 'SedanCX2',
                'type' => 'Sedan',
                'seats' => 4,
                'fuel_type' => 'Diesel',
                'engine_capacity' => 2200,
                'year' => 2017,
                'transmission' => 'Manuală',
                'image' => 'https://placehold.co/400x300?text=Sedan+7',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 70,
                    '3-7 days' => 65,
                    '8-22 days' => 60,
                    '23-45 days' => 55,
                    '46+ days' => 50,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ],
            [
                'model' => 'Hatchback sd2',
                'type' => 'Hatchback',
                'seats' => 5,
                'fuel_type' => 'BioDiesel',
                'engine_capacity' => 1300,
                'year' => 2016,
                'transmission' => 'Manuală',
                'image' => 'https://placehold.co/400x300?text=Hatchback+8',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 45,
                    '3-7 days' => 40,
                    '8-22 days' => 38,
                    '23-45 days' => 35,
                    '46+ days' => 30,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ],
            [
                'model' => 'Combi DC34',
                'type' => 'Combi',
                'seats' => 6,
                'fuel_type' => 'Diesel',
                'engine_capacity' => 2100,
                'year' => 2015,
                'transmission' => 'Automată',
                'image' => 'https://placehold.co/400x300?text=Combi+9',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 75,
                    '3-7 days' => 70,
                    '8-22 days' => 65,
                    '23-45 days' => 60,
                    '46+ days' => 55,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ],
            [
                'model' => 'SUV X20',
                'type' => 'SUV',
                'seats' => 7,
                'fuel_type' => 'Gas',
                'engine_capacity' => 3000,
                'year' => 2014,
                'transmission' => 'Manuală',
                'image' => 'https://placehold.co/400x300?text=SUV+10',
                'extra_images' => json_encode([
                    '1' => 'https://placehold.co/600x400?text=1',
                    '2' => 'https://placehold.co/600x400?text=2',
                    '3' => 'https://placehold.co/600x400?text=3'
                ]),
                'rental_prices' => json_encode([
                    '1-2 days' => 90,
                    '3-7 days' => 85,
                    '8-22 days' => 80,
                    '23-45 days' => 75,
                    '46+ days' => 70,
                ]),
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
