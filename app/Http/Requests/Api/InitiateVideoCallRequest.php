<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiateVideoCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow authenticated users to initiate calls
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                // Prevent calling yourself
                function ($attribute, $value, $fail) {
                    if ($value == $this->user()->id) {
                        $fail('You cannot call yourself');
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert receiver_id to integer if it's a string
        if ($this->has('receiver_id') && is_string($this->receiver_id)) {
            $this->merge([
                'receiver_id' => (int) $this->receiver_id,
            ]);
        }
    }
}
