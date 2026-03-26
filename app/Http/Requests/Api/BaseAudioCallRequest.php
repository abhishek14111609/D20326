<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;
use Illuminate\Foundation\Http\FormRequest;

class BaseAudioCallRequest extends FormRequest
{
    /**
     * The audio call instance.
     *
     * @var \App\Models\AudioCall
     */
    protected $call;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // For initiation, we don't have a call ID yet, so just check if user is authenticated
        if ($this->is('api/calls/audio/initiate')) {
            return $this->user() !== null;
        }

        // For other endpoints, check call authorization
        try {
            $callId = $this->route('callId') ?? $this->route('call_id');
            
            if (!$callId) {
                \Log::warning('No call ID found in route', [
                    'route_parameters' => $this->route() ? $this->route()->parameters() : [],
                    'user_id' => $this->user() ? $this->user()->id : null,
                ]);
                return false;
            }

            $this->call = AudioCall::where('call_id', $callId)
                ->where(function($query) {
                    $query->where('caller_id', $this->user()->id)
                          ->orWhere('receiver_id', $this->user()->id);
                })
                ->first();

            if (!$this->call) {
                \Log::warning('Call not found or user not authorized', [
                    'call_id' => $callId,
                    'user_id' => $this->user() ? $this->user()->id : null,
                ]);
                return false;
            }

            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error in BaseAudioCallRequest authorization', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'call_id' => $callId ?? null,
                'user_id' => $this->user() ? $this->user()->id : null,
            ]);
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Common rules for audio call requests
        ];
    }

    /**
     * Get the call instance.
     *
     * @return \App\Models\AudioCall|null
     */
    public function getCall()
    {
        return $this->call;
    }
}
