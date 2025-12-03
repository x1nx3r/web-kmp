<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Base notification service providing core notification functionality.
 *
 * This class handles the fundamental operations for notifications:
 * - Sending notifications to users
 * - Retrieving notifications
 * - Managing read status
 * - Cleanup operations
 */
class BaseNotificationService
{
    /**
     * Send a notification to a user.
     *
     * @param User $user The recipient
     * @param string $type The notification type
     * @param array $data The notification data (title, message, url, etc.)
     * @return string|null The notification ID or null on failure
     */
    public static function send(User $user, string $type, array $data): ?string
    {
        try {
            $id = Str::uuid()->toString();

            DB::table("notifications")->insert([
                "id" => $id,
                "type" => $type,
                "notifiable_type" => User::class,
                "notifiable_id" => $user->id,
                "data" => json_encode($data),
                "read_at" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ]);

            return $id;
        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage(), [
                "user_id" => $user->id,
                "type" => $type,
                "data" => $data,
            ]);
            return null;
        }
    }

    /**
     * Send a notification to multiple users.
     *
     * @param Collection|array $users
     * @param string $type
     * @param array $data
     * @return int Number of notifications sent
     */
    public static function sendToMany($users, string $type, array $data): int
    {
        $count = 0;
        foreach ($users as $user) {
            if (static::send($user, $type, $data)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Send notification to all users with a specific role.
     *
     * @param string $role
     * @param string $type
     * @param array $data
     * @return int Number of notifications sent
     */
    public static function sendToRole(string $role, string $type, array $data): int
    {
        $users = User::where("role", $role)->where("status", "aktif")->get();
        return static::sendToMany($users, $type, $data);
    }

    /**
     * Get unread notifications count for a user.
     *
     * @param User $user
     * @return int
     */
    public static function getUnreadCount(User $user): int
    {
        return DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->whereNull("read_at")
            ->count();
    }

    /**
     * Get notifications for a user.
     *
     * @param User $user
     * @param int $limit
     * @param bool $unreadOnly
     * @return Collection
     */
    public static function getNotifications(
        User $user,
        int $limit = 10,
        bool $unreadOnly = false
    ): Collection {
        $query = DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->orderBy("created_at", "desc")
            ->limit($limit);

        if ($unreadOnly) {
            $query->whereNull("read_at");
        }

        return $query->get()->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            return $notification;
        });
    }

    /**
     * Mark a notification as read.
     *
     * @param string $notificationId
     * @param User $user
     * @return bool
     */
    public static function markAsRead(string $notificationId, User $user): bool
    {
        return DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->update(["read_at" => now()]) > 0;
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param User $user
     * @return int Number of notifications marked as read
     */
    public static function markAllAsRead(User $user): int
    {
        return DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->whereNull("read_at")
            ->update(["read_at" => now()]);
    }

    /**
     * Delete old read notifications (cleanup).
     *
     * @param int $daysOld
     * @return int Number of notifications deleted
     */
    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        return DB::table("notifications")
            ->whereNotNull("read_at")
            ->where("read_at", "<", now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Delete a specific notification.
     *
     * @param string $notificationId
     * @param User $user
     * @return bool
     */
    public static function delete(string $notificationId, User $user): bool
    {
        return DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->delete() > 0;
    }

    /**
     * Get a notification by ID.
     *
     * @param string $notificationId
     * @param User $user
     * @return object|null
     */
    public static function find(string $notificationId, User $user): ?object
    {
        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->first();

        if ($notification) {
            $notification->data = json_decode($notification->data, true);
        }

        return $notification;
    }
}
