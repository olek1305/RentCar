<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class FilterOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $statuses = array_keys(Order::statuses());

        return [
            'status' => 'nullable|in:'.implode(',', $statuses),
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ];
    }
}
