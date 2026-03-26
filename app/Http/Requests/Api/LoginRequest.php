<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|string', // Can be email or mobile
            'device_name' => 'required|string',
            'device_token' => 'nullable|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert empty strings to null
        $this->merge([
            'device_token' => $this->device_token ?: null,
            'device_type' => $this->device_type ? strtolower($this->device_type) : null,
        ]);
    }
}
