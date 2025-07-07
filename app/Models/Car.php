<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'model',
        'type',
        'seats',
        'fuel_type',
        'engine_capacity',
        'year',
        'transmission',
        'main_image',
        'gallery_images',
        'image_url',
        'description',
        'rental_prices',
        'hidden'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'rental_prices' => 'array',
    ];

    public const TYPES = [
        'SEDAN',
        'COUPE',
        'SUV',
        'HATCHBACK',
        'CONVERTIBLE',
        'WAGON',
        'VAN',
        'PICKUP',
        'MINIVAN',
        'ROADSTER',
        'CROSSOVER',
        'LUXURY',
        'SPORTS CAR',
        'DIESEL',
        'ELECTRIC',
        'HYBRID'
    ];

    public const fuelTypes = [
        'GASOLINE',
        'DIESEL',
        'ELECTRIC',
        'HYBRID',
        'BIODIESEL',
        'CNG',
        'LPG',
        'HYDROGEN',
        'E85',
        'METHANOL',
    ];

    public const transmissions = [
        'AUTOMATIC',
        'MANUAL',
        'SEMI-AUTOMATIC'
    ];

    public function scopeVisible($query)
    {
        if (!auth()->check()) {
            return $query->where('hidden', false);
        }
        return $query;
    }
}
