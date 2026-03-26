<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChallengeRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'active', 'completed', 'cancelled'])],
            'type' => ['sometimes', 'string', Rule::in(['one_time', 'recurring', 'milestone'])],
            'target_count' => ['sometimes', 'integer', 'min:1'],
            'reward_points' => ['sometimes', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array']
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('start_date') && !$this->has('end_date')) {
            $this->merge([
                'end_date' => $this->end_date ?? $this->challenge->end_date,
            ]);
        } elseif ($this->has('end_date') && !$this->has('start_date')) {
            $this->merge([
                'start_date' => $this->start_date ?? $this->challenge->start_date,
            ]);
        }
    }
}
