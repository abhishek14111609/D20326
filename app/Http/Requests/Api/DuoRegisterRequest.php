<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DuoRegisterRequest extends FormRequest
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
            // Couple info
            'couple_name' => 'required|string|max:255',
            
            // Partner 1
            'partner1_name' => 'required|string|max:255',
            'partner1_email' => 'required|email|unique:users,email|max:255',
            'partner1_gender' => 'required|in:male,female,other',
            'partner1_mobile' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/|unique:user_profiles,mobile',
            'partner1_dob' => 'required|date|before:today',
            'partner1_bio' => 'nullable|string|max:500',
            'partner1_location' => 'nullable|string|max:255',
            'partner1_interest' => 'nullable|array',
            'partner1_interest.*' => 'string|max:50',
            'partner1_hobby' => 'nullable|array',
            'partner1_hobby.*' => 'string|max:50',
            'partner1_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Partner 2
            'partner2_name' => 'required|string|max:255',
            'partner2_email' => 'required|email|different:partner1_email|max:255|unique:users,email',
            'partner2_gender' => 'required|in:male,female,other',
            'partner2_mobile' => 'required|string|different:partner1_mobile|regex:/^\+?[1-9]\d{1,14}$/|unique:user_profiles,mobile',
            'partner2_dob' => 'required|date|before:today',
            'partner2_bio' => 'nullable|string|max:500',
            'partner2_location' => 'nullable|string|max:255',
            'partner2_interest' => 'nullable|array',
            'partner2_interest.*' => 'string|max:50',
            'partner2_hobby' => 'nullable|array',
            'partner2_hobby.*' => 'string|max:50',
            'partner2_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Optional main info
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'interest' => 'nullable|array',
            // 'interest.*' => 'string|max:50',
            'hobby' => 'nullable|array',
            // 'hobby.*' => 'string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'device_token' => 'nullable|string|max:500',
            'device_type' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',
            'last_login_ip' => 'nullable|string|max:500',
            'last_login_at' => 'nullable|string|max:500',
            'login_type' => 'nullable|string|in:email,social',
            'occupation' => 'nullable|string|max:50',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
			'latitude' => 'nullable|string',
			'longitude' => 'nullable|string',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'couple_name.required' => 'Couple name is required',
            'partner1_name.required' => 'Partner 1 name is required',
            'partner1_email.required' => 'Partner 1 email is required',
            'partner1_email.unique' => 'Partner 1 email is already registered',
            'partner1_gender.required' => 'Partner 1 gender is required',
            'partner1_dob.required' => 'Partner 1 date of birth is required',

            'partner2_name.required' => 'Partner 2 name is required',
            'partner2_email.required' => 'Partner 2 email is required',
            'partner2_email.different' => 'Partner 2 email must be different from partner 1 email',
            'partner2_email.unique' => 'Partner 2 email is already registered',
            'partner2_gender.required' => 'Partner 2 gender is required',
            'partner2_dob.required' => 'Partner 2 date of birth is required',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation()
    {
        $toArray = fn($value) => is_array($value) ? $value : ($value ? explode(',', $value) : []);
        
        $this->merge([
            'interest' => $toArray($this->interest),
            'hobby' => $toArray($this->hobby),
            'partner1_interest' => $toArray($this->partner1_interest),
            'partner1_hobby' => $toArray($this->partner1_hobby),
            'partner2_interest' => $toArray($this->partner2_interest),
            'partner2_hobby' => $toArray($this->partner2_hobby),
        ]);
    }

    /**
     * Handle a failed validation attempt
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
