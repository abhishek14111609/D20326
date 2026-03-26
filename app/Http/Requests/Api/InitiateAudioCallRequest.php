<?php

namespace App\Http\Requests\Api;

class InitiateAudioCallRequest extends BaseAudioCallRequest
{
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
                'exists:users,id',
                'not_in:' . $this->user()->id, // Can't call yourself
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'receiver_id.required' => 'The receiver ID is required.',
            'receiver_id.exists' => 'The selected receiver is invalid.',
            'receiver_id.not_in' => 'You cannot call yourself.',
        ];
    }
}
