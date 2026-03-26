<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use App\Http\Resources\Api\ApiResource;

class MessageResource extends ApiResource
{
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'message' => $this->message,
            'type' => $this->type,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toISOString(),
            'time_ago' => $this->created_at?->diffForHumans(),
            'is_sender' => $this->is_sender,
        ];

        // Add sender information
        if ($this->relationLoaded('sender')) {
            $response['sender'] = [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar' => $this->sender->profile->avatar_url ?? null,
            ];
        }

        // Add media information if this is a media message
        if ($this->type === 'media') {
            $response['media'] = [
                'url' => $this->media_url,
                'type' => $this->media_type,
                'mime_type' => $this->mime_type,
                'size' => $this->size,
                'width' => $this->width,
                'height' => $this->height,
                'duration' => $this->duration,
                'thumbnail' => $this->thumbnail_url,
            ];
        }

        return $this->formatResponse($response);
    }
}
