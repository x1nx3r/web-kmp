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
    /**
     * In-memory cache of active users per role, scoped to a single
     * request/command run.
     *
     * FIX (N+1): sendToRole() used to run `User::where('role', $role)->get()`
     * every single time it was called. Callers that loop over many rows
     * and call sendToRole() per row (e.g. PiutangNotificationService looping
     * over N overdue piutang, each notifying 2 roles) turned this into
     * 2*N identical queries per run, even though the user list for a given
     * role does not change during that run.
     *
     * We now fetch the user list once per role and reuse it for the rest
     * of the request/command lifecycle.
     *
     * Structure: [role => Collection<User>]
     */
    private static array $roleUsersCache = [];

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
        $rows = [];
        $now = now();
        foreach ($users as $user) {
            $rows[] = [
                "id" => Str::uuid()->toString(),
                "type" => $type,
                "notifiable_type" => User::class,
                "notifiable_id" => $user->id,
                "data" => json_encode($data),
                "read_at" => null,
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }
        if (empty($rows)) return 0;

        DB::table("notifications")->insert($rows); // 1 query untuk semua user

        foreach ($users as $user) {
            Cache::forget("notif_list_{$user->id}");
            Cache::forget("unread_notif_count_{$user->id}");
        }
        return count($rows);
    }

    /**
     * Send a notification to all active users with a given role.
     *
     * FIX (N+1): the user list per role is now fetched once and cached
     * in-memory for the duration of the request/command run, instead of
     * re-querying `User::where('role', $role)->get()` on every call.
     * This matters a lot for callers that invoke sendToRole() inside a
     * loop (e.g. one call per overdue piutang/invoice row).
     *
     * Note: this cache is static and process-lifetime only (safe for
     * queue workers / CLI commands that run once and exit; for long-lived
     * workers processing multiple unrelated jobs, consider resetting it
     * between jobs if role membership can change mid-run).
     */
    public static function sendToRole(string $role, string $type, array $data): int
    {
        return static::sendToMany(static::getCachedUsersByRole($role), $type, $data);
    }

    /**
     * Get all active users for a given role, cached in-memory for the
     * duration of the request/command run.
     *
     * Exposed publicly (not just used internally by sendToRole()) so that
     * callers who need custom filtering on top of "all users with role X"
     * (e.g. excluding the user who triggered the change) can still avoid
     * re-querying the same role membership from the DB every time they're
     * called in a loop. Filter the returned Collection in memory instead
     * of adding a WHERE clause, so the cached result stays reusable across
     * different callers/filters within the same run.
     *
     * Example:
     *   $marketing = BaseNotificationService::getCachedUsersByRole('marketing');
     *   if ($excludeId) {
     *       $marketing = $marketing->reject(fn ($u) => $u->id === $excludeId);
     *   }
     */
    public static function getCachedUsersByRole(string $role): Collection
    {
        if (!isset(self::$roleUsersCache[$role])) {
            self::$roleUsersCache[$role] = User::where("role", $role)
                ->where("status", "aktif")
                ->get();
        }

        return self::$roleUsersCache[$role];
    }

    /**
     * Clear the in-memory role->users cache.
     *
     * Useful for long-running processes (queue workers, scheduler daemons)
     * where role membership could change between jobs, or in tests that
     * need a fresh lookup.
     */
    public static function clearRoleUsersCache(): void
    {
        self::$roleUsersCache = [];
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