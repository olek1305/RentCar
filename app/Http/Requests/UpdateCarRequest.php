<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization check
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
            'model' => 'required|string|max:255|unique:cars,model,'.$this->car->id,
            'type' => 'required|string|in:Sedan,SUV,Hatchback,Coupe',
            'year' => 'required|integer|min:1900|',
            'seats' => 'required|integer|min:1|max:9',
            'fuel_type' => 'required|string|in:Gasoline,Diesel,Hybrid,Electric',
            'engine_capacity' => 'required|integer|min:500|max:8000',
            'transmission' => 'required|string|in:Manual,Automatic',
            'description' => 'nullable|string',
            'image' => 'nullable|url',        'main_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'daily_price' => 'required|numeric|min:1'
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'daily_price.min' => 'Daily price must be at least $1'
        ];
    }
}
