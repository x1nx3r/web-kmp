<?php

namespace App\Services\Notifications;

use App\Models\Penawaran;

/**
 * Notification service for Penawaran (Quotation) related notifications.
 *
 * Handles notifications for:
 * - Penawaran submission (to Direktur)
 * - Penawaran approval (to creator)
 * - Penawaran rejection (to creator)
 */
class PenawaranNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_SUBMITTED = "penawaran_submitted";
    public const TYPE_APPROVED = "penawaran_approved";
    public const TYPE_REJECTED = "penawaran_rejected";

    /**
     * Notify all direktur about a new penawaran submission.
     *
     * @param Penawaran $penawaran
     * @return int Number of notifications sent
     */
    public static function notifySubmitted(Penawaran $penawaran): int
    {
        $creator = $penawaran->createdBy;
        $creatorName = $creator ? $creator->nama : "Marketing";

        return static::sendToRole("direktur", self::TYPE_SUBMITTED, [
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
     * @param Penawaran $penawaran
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyApproved(Penawaran $penawaran): ?string
    {
        $creator = $penawaran->createdBy;
        if (!$creator) {
            return null;
        }

        $verifier = $penawaran->verifiedBy;
        $verifierName = $verifier ? $verifier->nama : "Direktur";

        return static::send($creator, self::TYPE_APPROVED, [
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
     * @param Penawaran $penawaran
     * @param string $reason The rejection reason
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyRejected(Penawaran $penawaran, string $reason): ?string
    {
        $creator = $penawaran->createdBy;
        if (!$creator) {
            return null;
        }

        $verifier = $penawaran->verifiedBy;
        $verifierName = $verifier ? $verifier->nama : "Direktur";

        return static::send($creator, self::TYPE_REJECTED, [
            "title" => "Penawaran Ditolak",
            "message" => "{$penawaran->nomor_penawaran} ditolak oleh {$verifierName}: {$reason}",
            "icon" => "times-circle",
            "icon_bg" => "bg-red-100",
            "icon_color" => "text-red-600",
            "url" => "/marketing/penawaran",
            "penawaran_id" => $penawaran->id,
            "nomor_penawaran" => $penawaran->nomor_penawaran,
            "reason" => $reason,
        ]);
    }
}
