<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterCarsRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', 'string', Rule::in(Car::TYPES)],
            'hidden' => 'nullable|in:all,visible,hidden',
        ];
    }
}
