<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;

class GetCallStatsRequest extends CallActionRequest
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
            
            // Only the caller or receiver can view call stats
            if (!in_array($user->id, [$call->caller_id, $call->receiver_id])) {
                $validator->errors()->add('call_id', 'You are not authorized to view statistics for this call.');
            }
            
            // Can't get stats for a call that hasn't started
            if (!$call->started_at) {
                $validator->errors()->add('call_id', 'This call has not started yet.');
            }
        });
    }
}
