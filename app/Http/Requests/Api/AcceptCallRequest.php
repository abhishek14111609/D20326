<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;

class AcceptCallRequest extends CallActionRequest
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
            
            if ($call->receiver_id !== $this->user()->id) {
                $validator->errors()->add('call_id', 'Only the call receiver can accept the call.');
            }
            
            if ($call->hasEnded()) {
                $validator->errors()->add('call_id', 'This call has already ended.');
            }
            
            if ($call->isInProgress()) {
                $validator->errors()->add('call_id', 'This call is already in progress.');
            }
        });
    }
}
