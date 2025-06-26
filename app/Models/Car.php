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
        'image_url',
        'description',
        'rental_prices'
    ];

    protected $casts = [
        'rental_prices' => 'array',
        'extra_images' => 'array'
    ];
}
