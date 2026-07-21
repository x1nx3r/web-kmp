<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BaseNotificationService
{
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

            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");

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

    public static function sendToRole(string $role, string $type, array $data): int
    {
        $users = User::where("role", $role)->where("status", "aktif")->get();
        return static::sendToMany($users, $type, $data);
    }

    public static function getUnreadCount(User $user): int
    {
        return DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->whereNull("read_at")
            ->count();
    }

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
            $notification->data = json_decode($notification->data, true) ?? [];
            return $notification;
        });
    }

    public static function markAsRead(string $notificationId, User $user): bool
    {
        $updated = DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->update(["read_at" => now()]) > 0;

        if ($updated) {
            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");
        }

        return $updated;
    }

    public static function markAllAsRead(User $user): int
    {
        $count = DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->whereNull("read_at")
            ->update(["read_at" => now()]);

        if ($count > 0) {
            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");
        }

        return $count;
    }

    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        return DB::table("notifications")
            ->whereNotNull("read_at")
            ->where("read_at", "<", now()->subDays($daysOld))
            ->delete();
    }

    public static function delete(string $notificationId, User $user): bool
    {
        $deleted = DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->delete() > 0;

        if ($deleted) {
            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");
        }

        return $deleted;
    }

    /**
     * Delete all read notifications for a user.
     *
     * @param User $user
     * @return int Number of notifications deleted
     */
    public static function deleteAllRead(User $user): int
    {
        $count = DB::table("notifications")
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->whereNotNull("read_at")
            ->delete();

        if ($count > 0) {
            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");
        }

        return $count;
    }

    public static function find(string $notificationId, User $user): ?object
    {
        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->where("notifiable_type", User::class)
            ->where("notifiable_id", $user->id)
            ->first();

        if ($notification) {
            $notification->data = json_decode($notification->data, true) ?? [];
        }

        return $notification;
    }
}