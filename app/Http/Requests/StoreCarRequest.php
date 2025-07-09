<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Changed from false to allow access
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'daily_price' => (float) $this->daily_price
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'model' => 'required|string|max:255|unique:cars,model',
            'type' => ['required', 'string', Rule::in(Car::TYPES)],
            'year' => 'required|integer|min:1880|max:' . date('Y'),
            'seats' => 'required|integer|min:1|max:9',
            'fuel_type' => ['required', 'string', Rule::in(Car::fuelTypes)],
            'engine_capacity' => 'required|integer|min:500|max:8000',
            'transmission' => ['required', 'string', Rule::in(Car::transmissions)],
            'description' => 'nullable|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'daily_price' => 'required|numeric|min:1'
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'model.unique' => 'This car model already exists',
            'daily_price.min' => 'Daily price must be at least $1'
        ];
    }
}
