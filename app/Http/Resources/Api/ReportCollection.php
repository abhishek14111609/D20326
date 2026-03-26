<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ReportResource::class;
    
    /**
     * The additional data that should be added to the top-level resource array.
     *
     * @var array
     */
    protected $additional = [
        'meta' => [
            'status' => 'success',
            'timestamp' => '',
            'stats' => [
                'total' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'rejected' => 0,
            ],
        ],
    ];
    
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        
        // Set the timestamp
        $this->additional['meta']['timestamp'] = now()->toIso8601String();
        
        // Check if the underlying resource is paginated
        $isPaginated = $this->resource instanceof \Illuminate\Pagination\AbstractPaginator;
        
        if ($isPaginated) {
            $this->additional['pagination'] = [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => (int) $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ],
            ];
        }
    }
    
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
    
    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        $isAdmin = $request->user() && $request->user()->hasRole('admin');
        
        // Get report statistics for the collection
        $isPaginated = $this->resource instanceof \Illuminate\Pagination\AbstractPaginator;
        $total = $isPaginated ? $this->resource->total() : $this->resource->count();
        
        $stats = [
            'total' => $total,
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
        ];
        
        // If this is an admin, include all stats
        if ($isAdmin) {
            $stats = [
                'total' => \App\Models\Report::count(),
                'pending' => \App\Models\Report::where('status', 'pending')->count(),
                'in_progress' => \App\Models\Report::where('status', 'in_progress')->count(),
                'resolved' => \App\Models\Report::where('status', 'resolved')->count(),
                'rejected' => \App\Models\Report::where('status', 'rejected')->count(),
            ];
        } 
        // If this is a regular user, only include their own stats
        elseif ($request->user()) {
            $stats = [
                'total' => \App\Models\Report::where('reporter_id', $request->user()->id)->count(),
                'pending' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->where('status', 'pending')
                    ->count(),
                'in_progress' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->where('status', 'in_progress')
                    ->count(),
                'resolved' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->where('status', 'resolved')
                    ->count(),
                'rejected' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->where('status', 'rejected')
                    ->count(),
            ];
        }
        
        // Update the additional data with the stats
        $this->additional['meta']['stats'] = $stats;
        
        return $this->additional;
    }
}
