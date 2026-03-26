<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;

class UserMembershipResource extends ApiResource
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
            'user_id' => $this->user_id,
            'membership_id' => $this->membership_id,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
           // 'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'is_active' => $this->isActive(),
            'days_remaining' => $this->days_remaining,
            'created_at' => $this->when($this->created_at, $this->created_at?->toISOString()),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->toISOString()),
            'membership' => new MembershipResource($this->whenLoaded('membership')),
            'user' => new UserResource($this->whenLoaded('user')),
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
