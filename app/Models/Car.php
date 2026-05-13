<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

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
        'hidden',
    ];

    protected $casts = [
        'hidden' => 'boolean',
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
        'SPORTS_CAR',
        'COMPACT',
        'LIMOUSINE',
    ];

    public const FUEL_TYPES = [
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

    public const TRANSMISSIONS = [
        'AUTOMATIC',
        'MANUAL',
        'SEMI-AUTOMATIC',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('hidden', false);
    }

    public function scopeHidden(Builder $query): Builder
    {
        return $query->where('hidden', true);
    }
}
