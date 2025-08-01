<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendContactRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns,strict',
                'max:255',
                function ($attribute, $value, $fail) {
                    $this->validateEmailDomain($value, $fail);
                }
            ],
            'message' => 'required|string|max:2000',
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
}
