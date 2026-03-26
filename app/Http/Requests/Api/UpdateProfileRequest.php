<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'interest' => 'nullable|array',
            'interest.*' => 'string|max:100',
            'hobby' => 'nullable|array',
            'hobby.*' => 'string|max:100',
            'relationship_status' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
            'languages' => 'nullable',
            'location' => 'nullable|string|max:255',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'gallery_images' => 'sometimes|array|max:10',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per file
            'gallery_images_remove' => 'sometimes|array',
            'gallery_images_remove.*' => 'string',
			'looking_for' => 'string',
			'ethnicity' => 'string',
			'address' => 'string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bio.max' => 'Bio must not exceed 1000 characters',
            'occupation.max' => 'Occupation must not exceed 255 characters',
            'languages.max' => 'Languages must not exceed 500 characters',
            'location.max' => 'Location must not exceed 255 characters',
            'gender.in' => 'Gender must be male, female, or other',
            'interest.*.max' => 'Each interest must not exceed 100 characters',
            'hobby.*.max' => 'Each hobby must not exceed 100 characters',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Debug: Log all request data
        \Log::info('Request Data:', $this->all());
        \Log::info('Files:', $this->allFiles());

        // Convert string inputs to arrays
        if ($this->has('interest') && is_string($this->interest)) {
            $this->merge([
                'interest' => array_filter(array_map('trim', explode(',', $this->interest)))
            ]);
        }

        if ($this->has('hobby') && is_string($this->hobby)) {
            $this->merge([
                'hobby' => array_filter(array_map('trim', explode(',', $this->hobby)))
            ]);
        }
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
