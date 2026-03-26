<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => 'https://duos.webvibeinfotech.in/storage/app/public/' . $this->image ??			               'https://duos.webvibeinfotech.in/public/assets/img/avatars/default-avatar.png',
            'start_date' => $this->start_date->toIso8601String(),
            'end_date' => $this->end_date->toIso8601String(),
            'status' => $this->status,
            'type' => $this->type,
            'target_count' => $this->target_count,
            'reward_points' => $this->reward_points,
            'rules' => $this->rules,
            'is_featured' => $this->is_featured,
            'participants_count' => $this->when(isset($this->participants_count), $this->participants_count),
            'is_active' => $this->isActive(),
            'is_upcoming' => $this->isUpcoming(),
            'is_completed' => $this->isCompleted(),
            'days_remaining' => $this->daysRemaining(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
