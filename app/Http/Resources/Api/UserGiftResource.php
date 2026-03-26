<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;

class UserGiftResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'gift_id' => $this->gift_id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message,
            'is_anonymous' => (bool) $this->is_anonymous,
            'created_at' => $this->when($this->created_at, $this->created_at?->toISOString()),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->toISOString()),
            'gift' => new GiftResource($this->whenLoaded('gift')),
            'sender' => $this->when(!$this->is_anonymous, new UserResource($this->whenLoaded('sender'))),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
        ];

        return $this->formatResponse($response);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'status' => 'success',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
