<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Notification types
     */
    public const TYPE_PENAWARAN_SUBMITTED = "penawaran_submitted";
    public const TYPE_PENAWARAN_APPROVED = "penawaran_approved";
    public const TYPE_PENAWARAN_REJECTED = "penawaran_rejected";

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
            \Log::error("Failed to send notification: " . $e->getMessage(), [
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
     * @param \Illuminate\Support\Collection|array $users
     * @param string $type
     * @param array $data
     * @return int Number of notifications sent
     */
    public static function sendToMany($users, string $type, array $data): int
    {
        $count = 0;
        foreach ($users as $user) {
            if (self::send($user, $type, $data)) {
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
    public static function sendToRole(
        string $role,
        string $type,
        array $data,
    ): int {
        $users = User::where("role", $role)->where("status", "aktif")->get();
        return self::sendToMany($users, $type, $data);
    }

    /**
     * Notify all direktur about a new penawaran submission.
     *
     * @param \App\Models\Penawaran $penawaran
     * @return int Number of notifications sent
     */
    public static function notifyPenawaranSubmitted($penawaran): int
    {
        $creator = $penawaran->createdBy;
        $creatorName = $creator ? $creator->nama : "Marketing";

        return self::sendToRole("direktur", self::TYPE_PENAWARAN_SUBMITTED, [
            "title" => "Penawaran Baru Menunggu Verifikasi",
            "message" => "{$penawaran->nomor_penawaran} dari {$creatorName} menunggu persetujuan Anda",
            "icon" => "file-invoice",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/marketing/penawaran",
            "penawaran_id" => $penawaran->id,
            "nomor_penawaran" => $penawaran->nomor_penawaran,
        ]);
    }

    /**
     * Notify the creator that their penawaran was approved.
     *
     * @param \App\Models\Penawaran $penawaran
     * @return string|null
     */
    public static function notifyPenawaranApproved($penawaran): ?string
    {
        $creator = $penawaran->createdBy;
        if (!$creator) {
            return null;
        }

        $verifier = $penawaran->verifiedBy;
        $verifierName = $verifier ? $verifier->nama : "Direktur";

        return self::send($creator, self::TYPE_PENAWARAN_APPROVED, [
            "title" => "Penawaran Disetujui",
            "message" => "{$penawaran->nomor_penawaran} telah disetujui oleh {$verifierName}",
            "icon" => "check-circle",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/marketing/penawaran",
            "penawaran_id" => $penawaran->id,
            "nomor_penawaran" => $penawaran->nomor_penawaran,
        ]);
    }

    /**
     * Notify the creator that their penawaran was rejected.
     *
     * @param \App\Models\Penawaran $penawaran
     * @param string $reason
     * @return string|null
     */
    public static function notifyPenawaranRejected(
        $penawaran,
        string $reason,
    ): ?string {
        $creator = $penawaran->createdBy;
        if (!$creator) {
            return null;
        }

        $verifier = $penawaran->verifiedBy;
        $verifierName = $verifier ? $verifier->nama : "Direktur";

        return self::send($creator, self::TYPE_PENAWARAN_REJECTED, [
            "title" => "Penawaran Ditolak",
            "message" => "{$penawaran->nomor_penawaran} ditolak oleh {$verifierName}: {$reason}",
            "icon" => "times-circle",
            "icon_bg" => "bg-red-100",
            "icon_color" => "text-red-600",
            "url" => "/marketing/riwayat-penawaran",
            "penawaran_id" => $penawaran->id,
            "nomor_penawaran" => $penawaran->nomor_penawaran,
            "reason" => $reason,
        ]);
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
     * @return \Illuminate\Support\Collection
     */
    public static function getNotifications(
        User $user,
        int $limit = 10,
        bool $unreadOnly = false,
    ) {
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
}
