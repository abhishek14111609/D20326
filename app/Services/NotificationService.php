<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a new notification
     *
     * @param User $user The user to receive the notification
     * @param string $type Notification type (use Notification::TYPE_* constants)
     * @param string $message Notification message
     * @param User|null $fromUser The user who triggered the notification (if any)
     * @param array $data Additional data to store with the notification
     * @return Notification
     */
    public function create(
        User $user,
        string $type,
        string $message,
        ?User $fromUser = null,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'from_user_id' => $fromUser ? $fromUser->id : null,
            'type' => $type,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Get user's notifications
     *
     * @param User $user
     * @param bool $unreadOnly Whether to get only unread notifications
     * @param int $perPage Number of notifications per page (0 for all)
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserNotifications(User $user, bool $unreadOnly = false, int $perPage = 15)
    {
        $query = $user->notifications()
            ->with('fromUser')
            ->latest();

        if ($unreadOnly) {
            $query->unread();
        }

        return $perPage > 0 
            ? $query->paginate($perPage)
            : $query->get();
    }

    /**
     * Mark notifications as read
     *
     * @param array $notificationIds Array of notification IDs to mark as read
     * @param User $user The user who owns the notifications
     * @return int Number of notifications updated
     */
    public function markAsRead(array $notificationIds, User $user): int
    {
        return $user->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read
     *
     * @param User $user
     * @return int Number of notifications updated
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications count for a user
     *
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Delete a notification
     *
     * @param int $notificationId
     * @param User $user
     * @return bool
     */
    public function delete(int $notificationId, User $user): bool
    {
        $notification = $user->notifications()->findOrFail($notificationId);
        return $notification->delete();
    }

    /**
     * Delete all notifications for a user
     *
     * @param User $user
     * @param bool $readOnly Delete only read notifications
     * @return int Number of deleted notifications
     */
    public function deleteAll(User $user, bool $readOnly = false): int
    {
        $query = $user->notifications();
        
        if ($readOnly) {
            $query->whereNotNull('read_at');
        }
        
        return $query->delete();
    }
}
