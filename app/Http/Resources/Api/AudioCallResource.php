<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioCallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->call_id,
            'caller_id' => $this->caller_id,
            'receiver_id' => $this->receiver_id,
            'status' => $this->status,
            'is_muted' => (bool) $this->is_muted,
            'duration' => $this->duration,
            'started_at' => $this->started_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'caller' => new UserResource($this->whenLoaded('caller')),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
        ];
    }
}
