<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;
use Illuminate\Foundation\Http\FormRequest;

class UnmuteCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // No specific rules needed here as we'll handle validation in the controller
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $call = $this->route('callId');
            $user = $this->user();
            
            // Get the call details
            $call = AudioCall::where('call_id', $call)
                ->where(function($query) use ($user) {
                    $query->where('caller_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->first();
                
            if (!$call) {
                $validator->errors()->add('call_id', 'Call not found or you do not have permission to modify this call.');
                return;
            }
            
            // Only allow unmuting if call is in progress
            if ($call->status !== AudioCall::STATUS_IN_PROGRESS) {
                $validator->errors()->add('call_id', 'The call is not in progress.');
            }
        });
    }
}
