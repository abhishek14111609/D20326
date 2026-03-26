<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseAudioCallRequest;
use App\Models\AudioCall;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GetAgoraTokenRequest extends BaseAudioCallRequest
{
   /**
     * The video call instance.
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
        try {
            $callId = $this->route('callId') ?? $this->route('call_id');
           
            if (!$callId) {
                Log::warning('❌ [GetAgoraTokenRequest] Missing callId in route');
                return false;
            }

            $this->call = AudioCall::where('call_id', $callId)
                ->where(function ($query) {
                    $query->where('caller_id', $this->user()->id)
                          ->orWhere('receiver_id', $this->user()->id);
                })
                ->first();

            if (!$this->call) {
                Log::warning('❌ [GetAgoraTokenRequest] Call not found or unauthorized', [
                    'call_id' => $callId,
                    'user_id' => $this->user() ? $this->user()->id : 'not_authenticated'
                ]);
                return false;
            }

            // Only allow getting token for active calls
            $allowedStatuses = [
                AudioCall::STATUS_INITIATED,
                AudioCall::STATUS_ACCEPTED,
            ];

            if (!in_array($this->call->status, $allowedStatuses)) {
                Log::warning('❌ [GetAgoraTokenRequest] Invalid call status', [
                    'call_id' => $callId,
                    'status' => $this->call->status,
                    'allowed_statuses' => $allowedStatuses
                ]);
                return false;
            }

            return true;
            
        } catch (\Exception $e) {
            Log::error('🔥 [GetAgoraTokenRequest] Authorization error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            // Add any validation rules here if needed
        ];
    }

    /**
     * Get the call instance.
     *
     * @return \App\Models\AudioCall|null
     */
    public function getCall()
    {
        // Try to load the call if not already loaded
        if (!$this->call) {
            $callId = $this->route('callId') ?? $this->route('call_id');
            if ($callId) {
                $this->call = AudioCall::where('call_id', $callId)->first();
            }
        }
        return $this->call;
    }
}