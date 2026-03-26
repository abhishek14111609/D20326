<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'admin_id',
        'type',
        'reason',
        'status',
        'reported_type',
        'reported_id',
        'evidence',
        'admin_notes',
        'action_taken',
        'additional_info',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'evidence' => 'array',
        'additional_info' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_editable',
        'is_resolved',
    ];

    /**
     * Report types.
     *
     * @var array
     */
    public const TYPES = [
        'user' => 'User Report',
        'content' => 'Inappropriate Content',
        'bug' => 'Bug Report',
        'other' => 'Other',
    ];

    /**
     * Report statuses.
     *
     * @var array
     */
    public const STATUSES = [
        'pending' => 'Pending Review',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];

    /**
     * Reported item types.
     *
     * @var array
     */
    public const REPORTED_TYPES = [
        'user' => 'User',
        'post' => 'Post',
        'comment' => 'Comment',
        'message' => 'Message',
        'profile' => 'Profile',
        'other' => 'Other',
    ];

    /**
     * Get the reporter (user who submitted the report).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the reported user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the admin assigned to the report.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the reported item (polymorphic relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reported()
    {
        return $this->morphTo('reported', 'reported_type', 'reported_id');
    }

    /**
     * Check if the report is editable.
     *
     * @return bool
     */
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    /**
     * Check if the report is resolved.
     *
     * @return bool
     */
    public function getIsResolvedAttribute(): bool
    {
        return in_array($this->status, ['resolved', 'rejected']);
    }

    /**
     * Get the human-readable type.
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get the human-reported type.
     *
     * @return string
     */
    public function getReportedTypeLabelAttribute(): string
    {
        return self::REPORTED_TYPES[$this->reported_type] ?? ucfirst($this->reported_type);
    }

    /**
     * Get the human-readable status.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Scope a query to only include pending reports.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in-progress reports.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include resolved reports.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope a query to only include rejected reports.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include reports of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include reports about a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAboutUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('reported_user_id', $userId)
                ->orWhere(function ($q) use ($userId) {
                    $q->where('reported_type', 'user')
                        ->where('reported_id', $userId);
                });
        });
    }

    /**
     * Mark the report as in progress.
     *
     * @param  int  $adminId
     * @return bool
     */
    public function markAsInProgress(int $adminId): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'admin_id' => $adminId,
        ]);
    }

    /**
     * Resolve the report.
     *
     * @param  string  $notes
     * @param  string  $action
     * @param  int  $adminId
     * @return bool
     */
    public function resolve(string $notes, string $action, int $adminId): bool
    {
        return $this->update([
            'status' => 'resolved',
            'admin_id' => $adminId,
            'admin_notes' => $notes,
            'action_taken' => $action,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Reject the report.
     *
     * @param  string  $reason
     * @param  int  $adminId
     * @return bool
     */
    public function reject(string $reason, int $adminId): bool
    {
        return $this->update([
            'status' => 'rejected',
            'admin_id' => $adminId,
            'admin_notes' => $reason,
            'resolved_at' => now(),
        ]);
    }
}
