<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntry extends JsonResource
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
            'rank' => $this->rank,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'photo' => $this->photo ?? null,
            'points' => $this->points ?? 0,
            'is_current_user' => $this->is_current_user ?? false,
        ];

        return $response;
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
