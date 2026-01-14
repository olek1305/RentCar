<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'car_id',
        'rental_date',
        'return_date',
        'rental_time',
        'return_time',
        'delivery_option',
        'delivery_address',
        'additional_insurance',
        'additional_insurance_cost',
        'additional_info',
        'status',
        'email_verification_token',
        'email_verification_sent_at',
        'email_verified_at',
        'sms_verification_token',
        'sms_verified_at',
        'sms_verification_sent_at',
        'payment_link_sent_at',
        'payment_amount',
        'payment_currency',
        'paid_at',
        'returned_at',
        'payment_session_id',
        'acceptance_terms',
        'acceptance_privacy',
        'final_payment_session_id',
        'final_payment_link_sent_at',
        'final_payment_amount',
        'final_paid_at',
    ];

    protected $casts = [
        'rental_date' => 'date',
        'return_date' => 'date',
        'rental_time' => 'string',
        'return_time' => 'string',
        'returned_at' => 'datetime',
        'delivery_option' => 'string',
        'delivery_address' => 'string',
        'additional_insurance' => 'boolean',
        'additional_insurance_cost' => 'float',
        'additional_info' => 'string',
        'status' => 'string',
        'email_verification_token' => 'string',
        'sms_verification_token' => 'string',
        'payment_amount' => 'float',
        'payment_currency' => 'string',
        'email_verified_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',
        'sms_verified_at' => 'datetime',
        'sms_verification_sent_at' => 'datetime',
        'payment_session_id' => 'string',
        'payment_link_sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'acceptance_terms' => 'boolean',
        'acceptance_privacy' => 'boolean',
        'final_payment_session_id' => 'string',
        'final_payment_link_sent_at' => 'datetime',
        'final_payment_amount' => 'float',
        'final_paid_at' => 'datetime',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public static function statuses(): array
    {
        return [
            'pending' => __('messages.status_pending'),
            'verified' => __('messages.status_verified'),
            'awaiting_payment' => __('messages.status_awaiting_payment'),
            'confirmed' => __('messages.status_confirmed'),
            'paid' => __('messages.status_paid'),
            'completed' => __('messages.status_completed'),
            'returned' => __('messages.status_returned'),
            'finished' => __('messages.status_finished'),
            'awaiting_final_payment' => __('messages.status_awaiting_final_payment'),
            'cancelled' => __('messages.status_cancelled'),
        ];
    }

    /**
     * Check if the order can receive a payment link
     */
    public function canSendPaymentLink(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Get reservation fee amount (5 EUR)
     */
    public function getReservationFee(): float
    {
        return 5.00; // 5 euro reservation fee
    }

    /**
     * Get static reservation fee amount (5 EUR)
     */
    public static function getStaticReservationFee(): float
    {
        return 5.00; // 5 euro reservation fee
    }

    /**
     * Get additional insurance cost
     */
    public function getAdditionalInsuranceCost(): float
    {
        return $this->additional_insurance ? ($this->additional_insurance_cost ?? 15.00) : 0.00;
    }

    /**
     * Get static additional insurance cost
     */
    public static function getStaticAdditionalInsuranceCost(): float
    {
        return 15.00; // 15 euro per day for additional insurance
    }

    /**
     * Get total payment amount including fees
     */
    public function calculateTotalAmount(): float
    {
        // If payment_amount is already set (for reservation fee), use it
        if ($this->payment_amount) {
            return (float) $this->payment_amount;
        }

        // Otherwise calculate based on car prices and extras
        $baseAmount = 0;

        if ($this->car && $this->car->rental_prices) {
            $prices = is_array($this->car->rental_prices)
                ? $this->car->rental_prices
                : json_decode($this->car->rental_prices, true);

            // For now, use the first available price as base
            $baseAmount = $prices['1-2'] ?? $prices[array_key_first($prices)] ?? 0;
        }

        $extraFee = 0;
        if ($this->delivery_option === 'airport') {
            $extraFee = 10; // Airport pickup fee
        } elseif ($this->delivery_option === 'delivery') {
            $extraFee = 20; // Delivery service fee
        }

        return $baseAmount + $extraFee;
    }

    /**
     * Check if the order can be marked as finished
     */
    public function canBeFinished(): bool
    {
        return in_array($this->status, ['paid', 'completed']);
    }

    /**
     * Calculate the final payment amount (full rental cost minus reservation fee)
     */
    public function calculateFinalPaymentAmount(): float
    {
        $rentalDays = $this->rental_date->diffInDays($this->return_date);
        $rentalDays = max(1, $rentalDays);

        $dailyRate = 0;

        if ($this->car && $this->car->rental_prices) {
            $prices = is_array($this->car->rental_prices)
                ? $this->car->rental_prices
                : json_decode($this->car->rental_prices, true);

            if ($rentalDays <= 2) {
                $dailyRate = $prices['1-2'] ?? 0;
            } elseif ($rentalDays <= 6) {
                $dailyRate = $prices['3-6'] ?? $prices['1-2'] ?? 0;
            } else {
                $dailyRate = $prices['7+'] ?? $prices['3-6'] ?? $prices['1-2'] ?? 0;
            }
        }

        $baseAmount = $dailyRate * $rentalDays;

        // Add delivery fee
        $deliveryFee = 0;
        if ($this->delivery_option === 'airport') {
            $deliveryFee = 10;
        } elseif ($this->delivery_option === 'delivery') {
            $deliveryFee = 20;
        }

        // Add insurance cost
        $insuranceCost = $this->getAdditionalInsuranceCost() * $rentalDays;

        $totalAmount = $baseAmount + $deliveryFee + $insuranceCost;

        // Subtract already paid reservation fee
        $reservationFee = $this->payment_amount ?? $this->getReservationFee();

        return max(0, $totalAmount - $reservationFee);
    }

    /**
     * Get the number of rental days
     */
    public function getRentalDays(): int
    {
        return max(1, $this->rental_date->diffInDays($this->return_date));
    }
}
