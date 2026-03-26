<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateDuoProfileRequest extends FormRequest
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
        $userId = $this->user() ? $this->user()->id : null;
        
        // Base rules for all fields (all optional for updates)
        $rules = [
            // Couple info
            'couple_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'relationship_status' => 'nullable|string|max:255',
            'languages' => 'nullable|array',
            'interest' => 'nullable|array',
            'interest.*' => 'string|max:50',
            'hobby' => 'nullable|array',
            'hobby.*' => 'string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Partner 1 fields
            'partner1_name' => 'nullable|string|max:255',
            'partner1_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'partner1_gender' => 'nullable|in:male,female,other',
            'partner1_mobile' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('user_profiles', 'mobile')->ignore($userId, 'user_id')
            ],
            'partner1_dob' => 'nullable|date|before:today',
            'partner1_bio' => 'nullable|string|max:500',
            'partner1_location' => 'nullable|string|max:255',
            'partner1_interest' => 'nullable|array',
            'partner1_interest.*' => 'string|max:50',
            'partner1_hobby' => 'nullable|array',
            'partner1_hobby.*' => 'string|max:50',
            'partner1_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Partner 2 fields
            'partner2_name' => 'nullable|string|max:255',
            'partner2_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')
            ],
            'partner2_gender' => 'nullable|in:male,female,other',
            'partner2_mobile' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('user_profiles', 'mobile')
            ],
            'partner2_dob' => 'nullable|date|before:today',
            'partner2_bio' => 'nullable|string|max:500',
            'partner2_location' => 'nullable|string|max:255',
            'partner2_interest' => 'nullable|array',
            'partner2_interest.*' => 'string|max:50',
            'partner2_hobby' => 'nullable|array',
            'partner2_hobby.*' => 'string|max:50',
            'partner2_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Add conditional required rules when partner1 fields are present
        if ($this->hasAny([
            'partner1_name', 'partner1_email', 'partner1_gender', 
            'partner1_mobile', 'partner1_dob'
        ])) {
            $rules['partner1_name'] = 'required|string|max:255';
            $rules['partner1_email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ];
            $rules['partner1_gender'] = 'required|in:male,female,other';
            $rules['partner1_mobile'] = [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('user_profiles', 'mobile')->ignore($userId, 'user_id')
            ];
            $rules['partner1_dob'] = 'required|date|before:today';
        }

        // Add conditional required rules when partner2 fields are present
        if ($this->hasAny([
            'partner2_name', 'partner2_email', 'partner2_gender', 
            'partner2_mobile', 'partner2_dob'
        ])) {
            $rules['partner2_name'] = 'required|string|max:255';
            $rules['partner2_email'] = [
                'required',
                'email',
                'different:partner1_email',
                'max:255',
                Rule::unique('users', 'email')
            ];
            $rules['partner2_gender'] = 'required|in:male,female,other';
            $rules['partner2_mobile'] = [
                'required',
                'string',
                'different:partner1_mobile',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('user_profiles', 'mobile')
            ];
            $rules['partner2_dob'] = 'required|date|before:today';
        }

        return $rules;
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            // Couple messages
            'couple_name.required' => 'Couple name is required',
            'couple_name.string' => 'Couple name must be a string',
            'couple_name.max' => 'Couple name may not be greater than 255 characters',

            // Partner 1 messages
            'partner1_name.required' => 'Partner 1 name is required',
            'partner1_email.required' => 'Partner 1 email is required',
            'partner1_email.email' => 'Partner 1 email must be a valid email address',
            'partner1_email.unique' => 'Partner 1 email is already registered',
            'partner1_gender.required' => 'Partner 1 gender is required',
            'partner1_gender.in' => 'Partner 1 gender must be male, female, or other',
            'partner1_mobile.required' => 'Partner 1 mobile number is required',
            'partner1_mobile.regex' => 'Partner 1 mobile number must be a valid format',
            'partner1_mobile.unique' => 'Partner 1 mobile number is already registered',
            'partner1_dob.required' => 'Partner 1 date of birth is required',
            'partner1_dob.date' => 'Partner 1 date of birth must be a valid date',
            'partner1_dob.before' => 'Partner 1 date of birth must be before today',

            // Partner 2 messages
            'partner2_name.required' => 'Partner 2 name is required',
            'partner2_email.required' => 'Partner 2 email is required',
            'partner2_email.email' => 'Partner 2 email must be a valid email address',
            'partner2_email.different' => 'Partner 2 email must be different from partner 1 email',
            'partner2_email.unique' => 'Partner 2 email is already registered',
            'partner2_gender.required' => 'Partner 2 gender is required',
            'partner2_gender.in' => 'Partner 2 gender must be male, female, or other',
            'partner2_mobile.required' => 'Partner 2 mobile number is required',
            'partner2_mobile.regex' => 'Partner 2 mobile number must be a valid format',
            'partner2_mobile.different' => 'Partner 2 mobile number must be different from partner 1',
            'partner2_mobile.unique' => 'Partner 2 mobile number is already registered',
            'partner2_dob.required' => 'Partner 2 date of birth is required',
            'partner2_dob.date' => 'Partner 2 date of birth must be a valid date',
            'partner2_dob.before' => 'Partner 2 date of birth must be before today',

            // File upload messages
            'partner1_photo.image' => 'Partner 1 photo must be an image',
            'partner1_photo.mimes' => 'Partner 1 photo must be a file of type: jpeg, png, jpg, gif',
            'partner1_photo.max' => 'Partner 1 photo may not be greater than 2048 kilobytes',
            'partner2_photo.image' => 'Partner 2 photo must be an image',
            'partner2_photo.mimes' => 'Partner 2 photo must be a file of type: jpeg, png, jpg, gif',
            'partner2_photo.max' => 'Partner 2 photo may not be greater than 2048 kilobytes',
            'avatar.image' => 'Avatar must be an image',
            'avatar.mimes' => 'Avatar must be a file of type: jpeg, png, jpg, gif',
            'avatar.max' => 'Avatar may not be greater than 2048 kilobytes',

            // Array fields
            'interest.array' => 'Interests must be an array',
            'hobby.array' => 'Hobbies must be an array',
            'partner1_interest.array' => 'Partner 1 interests must be an array',
            'partner1_hobby.array' => 'Partner 1 hobbies must be an array',
            'partner2_interest.array' => 'Partner 2 interests must be an array',
            'partner2_hobby.array' => 'Partner 2 hobbies must be an array',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation()
    {
        $toArray = function($value) {
            if (is_array($value)) {
                return $value;
            }
            if (is_string($value)) {
                // Try to decode as JSON first
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
                // Fall back to comma-separated values
                return array_filter(array_map('trim', explode(',', $value)));
            }
            return [];
        };

        $arrayFields = [
            'interest', 'hobby', 'languages',
            'partner1_interest', 'partner1_hobby',
            'partner2_interest', 'partner2_hobby'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => $toArray($this->input($field))
                ]);
            }
        }
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        // Log the validation errors and input for debugging
        \Log::error('Validation failed', [
            'errors' => $errors->all(),
            'input' => $this->all()
        ]);
        
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422));
    }
}
