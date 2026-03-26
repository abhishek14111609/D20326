<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;

class RejectCallRequest extends CallActionRequest
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
                $validator->errors()->add('call_id', 'Only the call receiver can reject the call.');
            }
            
            if ($call->hasEnded()) {
                $validator->errors()->add('call_id', 'This call has already ended.');
            }
        });
    }
}
