<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SettingCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = SettingResource::class;
    
    /**
     * The groups of settings.
     *
     * @var array
     */
    protected $groups = [];
    
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  array  $groups
     * @return void
     */
    public function __construct($resource, array $groups = [])
    {
        parent::__construct($resource);
        $this->groups = $groups;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // If we have specific groups, return the settings grouped by those groups
        if (!empty($this->groups)) {
            $grouped = [];
            
            foreach ($this->groups as $group) {
                $grouped[$group] = $this->collection->where('group', $group)
                    ->sortBy('sort_order')
                    ->values()
                    ->toArray($request);
            }
            
            return $grouped;
        }
        
        // Otherwise, return all settings grouped by their group
        return $this->collection->groupBy('group')
            ->map(function ($items) use ($request) {
                return $items->sortBy('sort_order')
                    ->values()
                    ->toArray($request);
            });
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
                'version' => '1.0',
                'api_url' => url('/api'),
                'settings_count' => $this->count(),
                'groups' => $this->groups ?: $this->collection->pluck('group')->unique()->values()->toArray(),
            ],
        ];
    }
}
