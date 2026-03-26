<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $isArray = is_array($this->resource);
        
        $response = [
            'id' => $isArray ? $this['id'] : $this->id,
            'title' => $isArray ? ($this['title'] ?? '-') : ($this->title ?? '-'),
            'type' => $isArray ? ($this['type'] ?? '-') : ($this->type ?? '-'),
            'is_muted' => $isArray ? (bool)($this['is_muted'] ?? false) : (bool)$this->is_muted,
            'unread_count' => $isArray ? ($this['unread_count'] ?? 0) : ($this->unread_count ?? 0),
            'created_at' => $isArray ? $this['created_at'] : $this->created_at,
            'updated_at' => $isArray ? $this['updated_at'] : $this->updated_at,
        ];

        // Handle latest message
        if ($isArray) {
            if (isset($this['latest_message'])) {
                $message = $this['latest_message'];
                $response['latest_message'] = [
                    'id' => $message['id'] ?? null,
                    'message' => $message['message'] ?? null,
                    'message_type' => $message['message_type'] ?? null,
                    'is_read' => (bool)($message['is_read'] ?? false),
                    'created_at' => $message['created_at'] ?? null,
                ];
            }
        } else {
            if ($this->relationLoaded('latestMessage') && $this->latestMessage) {
                $response['latest_message'] = [
                    'id' => $this->latestMessage->id,
                    'message' => $this->latestMessage->message,
                    'message_type' => $this->latestMessage->message_type,
                    'is_read' => (bool)$this->latestMessage->is_read,
                    'created_at' => $this->latestMessage->created_at->toDateTimeString(),
                ];
            }
        }

        // Handle participants if they exist
        if ($isArray) {
            if (isset($this['participants'])) {
                $response['participants'] = UserResource::collection($this['participants']);
            }
        } else if ($this->relationLoaded('participants')) {
            $response['participants'] = UserResource::collection($this->participants);
        }

        return $response;
    }

    /**
     * Get any additional data that should be returned with the resource array.
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
