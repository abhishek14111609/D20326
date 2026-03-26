<?php

namespace App\Http\Requests\Api;

use App\Models\VideoCall;
use Illuminate\Foundation\Http\FormRequest;

class VideoCallActionRequest extends FormRequest
{
    /**
     * The video call instance.
     *
     * @var \App\Models\VideoCall
     */
    protected $call;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $callId = $this->route('callId') ?? $this->route('call_id');
        
        if (!$callId) {
            return false;
        }

        $this->call = VideoCall::where('call_id', $callId)
            ->where(function($query) {
                $query->where('caller_id', $this->user()->id)
                      ->orWhere('receiver_id', $this->user()->id);
            })
            ->first();

        return (bool) $this->call;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Common rules for video call actions
        ];
    }

    /**
     * Get the video call instance.
     *
     * @return \App\Models\VideoCall|null
     */
    public function getCall()
    {
        return $this->call;
    }
}
