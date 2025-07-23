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
        $rules = [
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
            'verification_method' => 'required|in:sms,email',
        ];

        if ($this->input('verification_method') === 'sms') {
            $rules['sms_code'] = 'required|digits:5';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'sms_code.required_if' => __('The SMS verification code is required when using SMS verification'),
            'sms_code.digits' => __('The verification code must be 5 digits')
        ];
    }
}
