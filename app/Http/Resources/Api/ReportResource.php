<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;

class ReportResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $isAdmin = $request->user() && $request->user()->hasRole('admin');
        $isReporter = $request->user() && $request->user()->id === $this->reporter_id;
        
        $response = [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'is_editable' => $this->is_editable,
            'is_resolved' => $this->is_resolved,
            'created_at' => $this->when($this->created_at, $this->created_at?->toISOString()),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->toISOString()),
            'links' => [
                'self' => route('api.reports.show', $this->id),
            ],
        ];
        
        // Include reporter information
        $response['reporter'] = [
            'id' => $this->reporter_id,
            'name' => $this->whenLoaded('reporter', function () {
                return $this->reporter->name;
            }),
            'username' => $this->whenLoaded('reporter', function () {
                return $this->reporter->username;
            }),
            'avatar' => $this->whenLoaded('reporter', function () {
                return $this->reporter->avatar;
            }),
        ];
        
        // Include reported user information if available
        if ($this->reported_user_id) {
            $response['reported_user'] = [
                'id' => $this->reported_user_id,
                'name' => $this->whenLoaded('reportedUser', function () {
                    return $this->reportedUser->name;
                }),
                'username' => $this->whenLoaded('reportedUser', function () {
                    return $this->reportedUser->username;
                }),
                'avatar' => $this->whenLoaded('reportedUser', function () {
                    return $this->reportedUser->avatar;
                }),
            ];
        }
        
        // Include reported item information if available
        if ($this->reported_type && $this->reported_id) {
            $response['reported_item'] = [
                'type' => $this->reported_type,
                'type_label' => $this->reported_type_label,
                'id' => $this->reported_id,
            ];
        }
        
        // Include evidence if available
        if ($this->evidence) {
            $response['evidence'] = $this->evidence;
        }
        
        // Include additional info if available
        if ($this->additional_info) {
            $response['additional_info'] = $this->additional_info;
        }
        
        // Include admin-only fields
        if ($isAdmin) {
            $response['admin_notes'] = $this->when($this->admin_notes !== null, $this->admin_notes);
            $response['action_taken'] = $this->when($this->action_taken !== null, $this->action_taken);
            
            if ($this->resolved_at) {
                $response['resolved_at'] = $this->resolved_at->toIso8601String();
            }
            
            if ($this->admin_id) {
                $response['admin'] = [
                    'id' => $this->admin_id,
                    'name' => $this->whenLoaded('admin', function () {
                        return $this->admin->name;
                    }),
                    'username' => $this->whenLoaded('admin', function () {
                        return $this->admin->username;
                    }),
                ];
            }
        }
        
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
        $isAdmin = $request->user() && $request->user()->hasRole('admin');
        $isReporter = $request->user() && $request->user()->id === $this->reporter_id;
        
        $meta = [
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
        ];
        
        // Include report statistics for admin users
        if ($isAdmin) {
            $meta['stats'] = [
                'total_reports' => \App\Models\Report::count(),
                'pending_reports' => \App\Models\Report::where('status', 'pending')->count(),
                'in_progress_reports' => \App\Models\Report::where('status', 'in_progress')->count(),
            ];
        }
        
        // Include user-specific statistics
        if ($isReporter) {
            $meta['user_stats'] = [
                'total_reports' => \App\Models\Report::where('reporter_id', $request->user()->id)->count(),
                'pending_reports' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->where('status', 'pending')
                    ->count(),
                'resolved_reports' => \App\Models\Report::where('reporter_id', $request->user()->id)
                    ->whereIn('status', ['resolved', 'rejected'])
                    ->count(),
            ];
        }
        
        return [
            'meta' => $meta,
        ];
    }
    
    private function formatResponse($response)
    {
        return array_map(function ($value) {
            return $value === null ? '-' : $value;
        }, $response);
    }
}
