<?php

namespace App\Http\Requests\Api;

use App\Models\AudioCall;
use Illuminate\Validation\Rule;

class CallActionRequest extends BaseAudioCallRequest
{
    /**
     * The call instance.
     *
     * @var \App\Models\AudioCall
     */
    protected $call;

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->call = $this->getCall();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // No additional fields needed for these actions
        ];
    }

    /**
     * Get the call instance.
     *
     * @return \App\Models\AudioCall
     */
    public function getCallInstance()
    {
        return $this->call;
    }
}
