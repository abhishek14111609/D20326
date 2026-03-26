<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'message' => $this->message,
            'is_read' => !is_null($this->read_at),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'data' => $this->data ?? [],
            'from_user' => $this->whenLoaded('fromUser', function () {
                return [
                    'id' => $this->fromUser->id,
                    'name' => $this->fromUser->name,
                    'avatar' => $this->fromUser->avatar_url ?? null,
                ];
            }),
        ];
    }
}
