<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only admin users can update settings
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => [
                'required',
                'string',
                'max:100',
                Rule::exists('settings', 'key'),
            ],
            'value' => [
                'required',
                'string',
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
                'in:string,text,number,boolean,url,timezone,json,array,select,radio,checkbox',
            ],
            'group' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'display_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_public' => [
                'sometimes',
                'required',
                'boolean',
            ],
            'options' => [
                'nullable',
                'array',
            ],
            'sort_order' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'key.exists' => 'The specified setting key does not exist.',
            'value.required' => 'The value field is required.',
            'type.in' => 'The selected type is invalid. Must be one of: string, text, number, boolean, url, timezone, json, array, select, radio, checkbox',
        ];
    }
}
