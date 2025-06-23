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
                'description' => 'Skoda Superb SportLine 2019 combină eleganța cu performanța sportivă. Cu un design dinamic și elemente sportive distincte, acest model oferă un interior spațios, tehnologie avansată și confort superior. Dotat cu motoare puternice și eficiente, Superb SportLine asigură o experiență de condus captivantă și rafinată, ideală pentru entuziaști.',
            ],
            [
                'model' => 'Sedan C5',
                'type' => 'Sedan',
                'seats' => 4,
                'fuel_type' => 'Petrol',
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
                'description' => 'Robust SUV perfect pentru aventuri în familie și terenuri variate, cu spațiu amplu și performanțe bune.',
            ],
            [
                'model' => 'Hatchback V32',
                'type' => 'Hatchback',
                'seats' => 5,
                'fuel_type' => 'Petrol',
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
                'description' => 'Compact și economic, hatchback-ul este ideal pentru oraș și drumuri scurte, cu manevrabilitate excelentă.',
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
                'description' => 'Model electric modern, combinând eficiența energetică cu spațiul și confortul unui combi.',
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
                'description' => 'SUV hibrid ce oferă un mix perfect între putere și economie, ideal pentru călătorii lungi și oraș.',
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
                'description' => 'Sedan clasic cu performanțe solide și confort sporit, perfect pentru deplasări zilnice.',
            ],
            [
                'model' => 'Hatchback sd2',
                'type' => 'Hatchback',
                'seats' => 5,
                'fuel_type' => 'Petrol',
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
                'description' => 'Hatchback economic și agil, ideal pentru oraș și parcări strâmte.',
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
                'description' => 'Combi spațios, ideal pentru familii și bagaje multe, cu consum eficient.',
            ],
            [
                'model' => 'SUV X20',
                'type' => 'SUV',
                'seats' => 7,
                'fuel_type' => 'Petrol',
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
                'description' => 'SUV puternic, cu motor mare și transmisie manuală, pentru șoferii care preferă control total.',
            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
