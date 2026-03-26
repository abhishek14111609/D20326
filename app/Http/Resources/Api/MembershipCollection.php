<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MembershipCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = MembershipResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $response = [
            'data' => $this->collection,
        ];

        // Check if the underlying resource is paginated
        $isPaginated = $this->resource instanceof \Illuminate\Pagination\AbstractPaginator;
        
        if ($isPaginated) {
            $response['meta'] = [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
            ];
        } else {
            // For non-paginated collections
            $response['meta'] = [
                'total' => $this->collection->count(),
                'count' => $this->collection->count(),
                'per_page' => $this->collection->count(),
                'current_page' => 1,
                'total_pages' => 1,
            ];
        }

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
