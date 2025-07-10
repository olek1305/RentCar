<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9 ]+$/|min:9',
            'car_id' => 'required|exists:cars,id',
            'rental_date' => 'required|date|after_or_equal:today',
            'rental_time_hour' => 'required|string|size:2',
            'rental_time_minute' => 'required|string|size:2',
            'return_time_hour' => 'required|string|size:2',
            'return_time_minute' => 'required|string|size:2',
            'extra_delivery_fee' => 'boolean',
            'airport_delivery' => 'boolean',
            'additional_info' => 'nullable|string',
        ];
    }
}
