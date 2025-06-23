<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'model',
        'seats',
        'fuel_type',
        'engine_capacity',
        'year',
        'transmission',
        'image_url',
        'description',
    ];

    protected $casts = [
        'rental_prices' => 'array',
    ];
}
