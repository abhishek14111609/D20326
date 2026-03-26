<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Get a paginated list of reports based on filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getReports(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Report::with(['reporter', 'reportedUser', 'admin']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (!empty($filters['reporter_id'])) {
            $query->where('reporter_id', $filters['reporter_id']);
        }
        
        // Order by most recent first
        $query->latest();
        
        return $query->paginate($perPage);
    }
    
    /**
     * Get a single report by ID.
     *
     * @param int $id
     * @return Report
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getReport(int $id): Report
    {
        return Report::with(['reporter', 'reportedUser', 'admin'])->findOrFail($id);
    }
    
    /**
     * Check if a user can view a specific report.
     *
     * @param User $user
     * @param Report $report
     * @return bool
     */
    public function canViewReport(User $user, Report $report): bool
    {
        // Admins can view any report
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Reporters can view their own reports
        return $user->id === $report->reporter_id;
    }
    
    /**
     * Create a new report.
     *
     * @param int $reporterId
     * @param array $data
     * @return Report
     * @throws \Exception
     */
    public function createReport(int $reporterId, array $data): Report
    {
        try {
            DB::beginTransaction();
            
            $report = new Report([
                'reporter_id' => $reporterId,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'status' => 'pending', // Default status
            ]);
            
            // Set reported item details if provided
            if (!empty($data['reported_type']) && !empty($data['reported_id'])) {
                $report->reported_type = $data['reported_type'];
                $report->reported_id = $data['reported_id'];
                
                // If reporting a user, store the reported user ID
                if ($data['reported_type'] === 'user') {
                    $report->reported_user_id = $data['reported_id'];
                }
            }
            
            // Handle evidence (array of URLs)
            if (!empty($data['evidence'])) {
                $report->evidence = $data['evidence'];
            }
            
            // Store additional info as JSON
            if (!empty($data['additional_info'])) {
                $report->additional_info = $data['additional_info'];
            }
            
            $report->save();
            
            // Log the report creation
            Log::info("Report #{$report->id} created by user #{$reporterId}");
            
            // TODO: Notify admins about the new report
            
            DB::commit();
            
            return $report->load(['reporter', 'reportedUser']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create report: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update an existing report.
     *
     * @param Report $report
     * @param array $data
     * @param User $updatedBy
     * @return Report
     * @throws \Exception
     */
    public function updateReport(Report $report, array $data, User $updatedBy): Report
    {
        try {
            DB::beginTransaction();
            
            $originalStatus = $report->status;
            $statusChanged = false;
            
            // Update report fields
            $updatableFields = [
                'status', 'reason', 'admin_notes', 'action_taken', 
                'evidence', 'additional_info', 'reported_type', 'reported_id'
            ];
            
            foreach ($updatableFields as $field) {
                if (array_key_exists($field, $data)) {
                    // Check if status is being changed
                    if ($field === 'status' && $report->status !== $data['status']) {
                        $statusChanged = true;
                    }
                    
                    $report->{$field} = $data[$field];
                }
            }
            
            // If the report is being assigned to an admin
            if ($statusChanged && $updatedBy->hasRole('admin')) {
                $report->admin_id = $updatedBy->id;
                
                // If the report is being resolved, set the resolved_at timestamp
                if ($data['status'] === 'resolved' || $data['status'] === 'rejected') {
                    $report->resolved_at = now();
                }
            }
            
            $report->save();
            
            // Log the update
            $logMessage = "Report #{$report->id} updated by user #{$updatedBy->id}";
            if ($statusChanged) {
                $logMessage .= ", status changed from {$originalStatus} to {$data['status']}";
                
                // TODO: Notify the reporter about status change
            }
            
            Log::info($logMessage);
            
            DB::commit();
            
            return $report->load(['reporter', 'reportedUser', 'admin']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update report #{$report->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete a report.
     *
     * @param Report $report
     * @return bool
     * @throws \Exception
     */
    public function deleteReport(Report $report): bool
    {
        try {
            $reportId = $report->id;
            $report->delete();
            
            Log::info("Report #{$reportId} deleted");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to delete report #{$report->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get report statistics.
     *
     * @return array
     */
    public function getReportStats(): array
    {
        $total = Report::count();
        $pending = Report::where('status', 'pending')->count();
        $inProgress = Report::where('status', 'in_progress')->count();
        $resolved = Report::where('status', 'resolved')->count();
        $rejected = Report::where('status', 'rejected')->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'resolution_rate' => $total > 0 ? round((($resolved + $rejected) / $total) * 100, 2) : 0,
        ];
    }
    
    /**
     * Get reports by a specific reporter.
     *
     * @param int $reporterId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReportsByReporter(int $reporterId, int $limit = 10)
    {
        return Report::where('reporter_id', $reporterId)
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get reports about a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReportsAboutUser(int $userId, int $limit = 10)
    {
        return Report::where('reported_user_id', $userId)
            ->orWhere(function (Builder $query) use ($userId) {
                $query->where('reported_type', 'user')
                    ->where('reported_id', $userId);
            })
            ->latest()
            ->limit($limit)
            ->get();
    }
}
