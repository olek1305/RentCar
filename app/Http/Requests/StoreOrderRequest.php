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
            'email' => [
                'required',
                'email:rfc,dns,strict',
                'max:255',
                function ($attribute, $value, $fail) {
                    $this->validateEmailDomain($value, $fail);
                }
            ],
            'phone' => 'required|regex:/^[0-9 ]+$/|min:9',
            'car_id' => 'required|exists:cars,id',
            'rental_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:rental_date',
            'rental_time_hour' => 'required|string|size:2',
            'rental_time_minute' => 'required|string|size:2',
            'return_time_hour' => 'required|string|size:2',
            'return_time_minute' => 'required|string|size:2',
            'delivery_option' => 'required|in:pickup,airport,delivery',
            'delivery_address' => 'required_if:delivery_option,delivery|nullable|string|max:500',
            'additional_insurance' => 'sometimes|boolean',
            'additional_info' => 'nullable|string',
            'verification_method' => 'required|in:sms,email',
            'acceptance_terms' => 'required|accepted',
            'acceptance_privacy' => 'required|accepted',
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
            'sms_code.digits' => __('The verification code must be 5 digits'),
            'delivery_address.required_if' => __('The delivery address is required when selecting delivery service'),
            'acceptance_terms.required' => __('You must accept the terms of service'),
            'acceptance_terms.accepted' => __('You must accept the terms of service'),
            'acceptance_privacy.required' => __('You must accept the privacy policy'),
            'acceptance_privacy.accepted' => __('You must accept the privacy policy'),
        ];
    }

    protected function validateEmailDomain($email, $fail): void
    {
        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            $fail('The email format is invalid.');
            return;
        }

        $domain = $parts[1];

        // Check for common TLD issues
        if (preg_match('/\.{2,}/', $domain) ||
            preg_match('/^\.|\.$/', $domain) ||
            !preg_match('/\.[a-z]{2,}$/i', $domain)) {
            $fail('The email domain is invalid.');
            return;
        }

        // Check DNS MX records
        if (!checkdnsrr($domain, 'MX')) {
            $fail('The email domain does not exist.');
        }
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox value to boolean
        $this->merge([
            'additional_insurance' => $this->boolean('additional_insurance'),
        ]);
    }

}
