<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'currency_code' => 'required|string|size:3|unique:currency_settings,currency_code',
            'currency_symbol' => 'required|string|max:5',
            'currency_name' => 'required|string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('currency_code')) {
            $this->merge([
                'currency_code' => strtoupper((string) $this->input('currency_code')),
            ]);
        }
    }
}
