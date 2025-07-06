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

    public function scopeVisible($query)
    {
        if (!auth()->check()) {
            return $query->where('hidden', false);
        }
        return $query;
    }
}
