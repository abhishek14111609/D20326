<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CompetitionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Helper function to convert null to empty string
        $nullToEmpty = function ($value) {
            return is_null($value) ? '' : $value;
        };

        return [
            'id' => $this->id,
            'title' => $nullToEmpty($this->title),
            'description' => $nullToEmpty($this->description),
            'image_url' => $this->getImageUrl($this->image, 'competitions/images/default-competition.png') ?? '',
            'banner_image_url' => $this->getImageUrl($this->banner_image, 'competitions/banners/default-banner.png') ?? '',
            'registration_start' => $nullToEmpty($this->registration_start),
            'registration_end' => $nullToEmpty($this->registration_end),
            'start_date' => $nullToEmpty($this->start_date),
            'end_date' => $nullToEmpty($this->end_date),
            'status' => $nullToEmpty($this->status),
            'type' => $nullToEmpty($this->competition_type),
            'max_participants' => $nullToEmpty($this->max_participants),
            'min_participants' => $nullToEmpty($this->min_participants),
            'entry_fee' => $nullToEmpty($this->entry_fee),
            'currency' => $nullToEmpty($this->currency ?? 'USD'),
            'is_featured' => $this->is_featured ?? false,
            'timezone' => $nullToEmpty($this->timezone),
            'created_at' => $nullToEmpty($this->created_at),
            'updated_at' => $nullToEmpty($this->updated_at),
        ];
    }

    /**
     * Get the full URL for an image with fallback to default
     *
     * @param string|null $path
     * @param string $defaultPath
     * @return string|null
     */
    protected function getImageUrl($path, $defaultPath)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return 'https://duos.webvibeinfotech.in/storage/app/public/' . $path;
        }
        
        // Check if default exists, otherwise return null
        return 'https://duos.webvibeinfotech.in/storage/app/public/' . $defaultPath;
    }
}
