<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'car_id',
        'rental_date',
        'rental_time',
        'return_time',
        'extra_delivery_fee',
        'airport_delivery',
        'additional_info',
        'status',
        'email_verification_token',
        'email_verified_at',
        'sms_verification_code',
        'phone_verified_at'
    ];

    protected $casts = [
        'rental_date' => 'date',
        'rental_time' => 'string',
        'return_time' => 'string',
        'extra_delivery_fee' => 'boolean',
        'airport_delivery' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public static function statuses(): array
    {
        return [
            'pending_verification' => 'Oczekiwanie na Weryfikację',
            'pending' => 'Oczekujące',
            'confirmed' => 'Potwierdzone',
            'completed' => 'Zakończone',
            'cancelled' => 'Anulowane'
        ];
    }
}
