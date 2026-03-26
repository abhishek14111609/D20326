<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChallengeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'completed', 'cancelled'])],
            'type' => ['required', 'string', Rule::in(['one_time', 'recurring', 'milestone'])],
            'target_count' => ['required', 'integer', 'min:1'],
            'reward_points' => ['required', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array']
        ];
    }
}
