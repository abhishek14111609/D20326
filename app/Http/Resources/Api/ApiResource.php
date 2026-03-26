<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    /**
     * Convert null values to dashes in the given array
     *
     * @param array $data
     * @return array
     */
    protected function nullToDash(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->nullToDash($value);
            }
            return $value ?? '-';
        }, $data);
    }

    /**
     * Format the response data with null-to-dash conversion
     *
     * @param array $data
     * @return array
     */
    protected function formatResponse(array $data): array
    {
        return $this->nullToDash($data);
    }
}
