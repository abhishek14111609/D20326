<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'mobile' => [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('user_profiles', 'mobile'),
            ],
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date|before:today',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',

            // FORM-DATA flexible fields
            'interest' => 'nullable',
            'hobby' => 'nullable',
            'languages' => 'nullable',

            'occupation' => 'nullable|string|max:255',

            // Image fields
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
           	'gallery_images'   => 'nullable|array',
			'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'registration_type' => 'nullable|in:single,duo',
            'device_token' => 'nullable|string|max:500',
            'device_type' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',

            // Optional social fields
            'social_provider' => 'nullable|in:google,facebook,apple',
            'social_id' => 'nullable|string',
            'access_token' => 'nullable|string',
            'identity_token' => 'nullable|string',
			'looking_for' => 'nullable|string',
			'ethnicity' => 'nullable|string',
			'address' => 'nullable|string',
			'latitude' => 'nullable|string',
			'longitude' => 'nullable|string',
        ];

        // Duo type extra rules
        if ($this->input('registration_type') === 'duo') {
            $rules = array_merge($rules, [
                'couple_name' => 'required|string|max:255',
                'partner1_name' => 'required|string|max:255',
                'partner1_email' => 'required|email|different:email',
                'partner1_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'partner2_name' => 'required|string|max:255',
                'partner2_email' => 'required|email|different:email,partner1_email',
                'partner2_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        // 🔹 Convert comma separated or JSON strings into arrays for form-data inputs
        $fieldsToParse = ['hobby', 'interest', 'languages'];

        foreach ($fieldsToParse as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);

                if (is_string($value)) {
                    // Try JSON decode first
                    $parsed = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $this->merge([$field => $parsed]);
                    } else {
                        // Convert comma-separated string → array
                        $this->merge([
                            $field => array_map('trim', explode(',', $value))
                        ]);
                    }
                }
            }
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'mobile.required' => 'Mobile number is required',
            'mobile.unique' => 'This mobile number is already registered',
            'email.unique' => 'This email is already registered',
            'gender.in' => 'Gender must be male, female, or other',
            'dob.before' => 'Date of birth must be before today',
            'avatar.image' => 'Avatar must be a valid image file',
            'avatar.mimes' => 'Avatar must be jpeg, png, jpg, or gif format',
            'avatar.max' => 'Avatar must not exceed 2048KB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
