<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;

class EndCallRequest extends CallActionRequest
{
    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $call = $this->getCallInstance();
            $user = $this->user();
            
            // Only the caller or receiver can end the call
            if (!in_array($user->id, [$call->caller_id, $call->receiver_id])) {
                $validator->errors()->add('call_id', 'You are not authorized to end this call.');
            }
            
            // Can't end a call that's already ended
            if ($call->hasEnded()) {
                $validator->errors()->add('call_id', 'This call has already ended.');
            }
            
            // If the call hasn't been accepted yet, only the caller can end it
            if (!$call->isInProgress() && $call->caller_id !== $user->id) {
                $validator->errors()->add('call_id', 'Only the caller can end a call that hasn\'t been accepted yet.');
            }
        });
    }
}
