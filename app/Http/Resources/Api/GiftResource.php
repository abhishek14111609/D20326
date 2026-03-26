<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class GiftResource extends ApiResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => 'https://duos.webvibeinfotech.in/storage/app/public/' . $this->image_url,
            'price' => $this->price !== null ? (float) $this->price : null,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->whenLoaded('category', function () {
                    return $this->category->name;
                }),
                'icon_url' => $this->whenLoaded('category', function () {
                    return $this->category->icon_url;
                }),
            ],
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->when($this->created_at, $this->created_at?->toISOString()),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->toISOString()),
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
