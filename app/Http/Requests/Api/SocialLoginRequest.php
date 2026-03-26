<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SocialLoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'access_token' => 'required_without:identity_token|string',
            'identity_token' => 'required_without:access_token|string', // For Apple
            'authorization_code' => 'nullable|string', // For Apple
            'device_type' => 'nullable|in:android,ios,web',
            'device_token' => 'nullable|string',
            'registration_type' => 'required|in:single,duo'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'access_token.required_without' => 'Access token is required for social login',
            'identity_token.required_without' => 'Identity token is required for Apple login',
            'registration_type.required' => 'Registration type is required',
            'registration_type.in' => 'Registration type must be single or duo',
            'device_type.in' => 'Device type must be android, ios, or web'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
