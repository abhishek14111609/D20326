<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;

class MuteUnmuteCallRequest extends CallActionRequest
{
    /**
     * Determine if the request is for muting or unmuting.
     *
     * @var bool
     */
    protected $shouldMute;

    /**
     * Create a new request instance.
     *
     * @param  array  $query  The GET parameters
     * @param  array  $request  The POST parameters
     * @param  array  $attributes  The route parameters
     * @param  array  $cookies  The cookies
     * @param  array  $files  The uploaded files
     * @param  array  $server  The server variables
     * @param  mixed  $content  The raw body data
     * @param  bool  $shouldMute  Whether to mute (true) or unmute (false)
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null,
        bool $shouldMute = true
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->shouldMute = $shouldMute;
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
            $call = $this->getCallInstance();
            $user = $this->user();
            
            // Only the caller or receiver can mute/unmute the call
            if (!in_array($user->id, [$call->caller_id, $call->receiver_id])) {
                $validator->errors()->add('call_id', 'You are not authorized to modify this call.');
            }
            
            // Can't mute/unmute a call that's not in progress
            if (!$call->isInProgress()) {
                $validator->errors()->add('call_id', 'The call is not in progress.');
            }
            
            // Check if the call is already in the desired state
            if ($this->shouldMute && $call->is_muted) {
                $validator->errors()->add('call_id', 'The call is already muted.');
            } elseif (!$this->shouldMute && !$call->is_muted) {
                $validator->errors()->add('call_id', 'The call is not muted.');
            }
        });
    }
}
