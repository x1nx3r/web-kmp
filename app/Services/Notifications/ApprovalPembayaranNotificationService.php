<?php

namespace App\Services\Notifications;

use App\Models\ApprovalPembayaran;
use App\Models\User;

/**
 * Notification service for Approval Pembayaran notifications.
 *
 * Handles notifications for:
 * - New approval pembayaran created (pending) - notifies accounting team
 * - Approval pembayaran approved - notifies relevant parties
 * - Approval pembayaran rejected - notifies submitter
 */
class ApprovalPembayaranNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_PENDING_APPROVAL = "approval_pembayaran_pending";
    public const TYPE_APPROVED = "approval_pembayaran_approved";
    public const TYPE_REJECTED = "approval_pembayaran_rejected";

    /**
     * Notify Accounting team that there's a new approval pembayaran pending.
     *
     * @param ApprovalPembayaran $approval
     * @return int Number of notifications sent
     */
    public static function notifyPendingApproval(ApprovalPembayaran $approval): int
    {
        $pengiriman = $approval->pengiriman;

        if (!$pengiriman) {
            return 0;
        }

        $noPengiriman = $pengiriman->no_pengiriman ?? '-';
        $poNumber = $pengiriman->purchaseOrder->po_number ?? '-';
        $amount = number_format($pengiriman->total_harga_kirim ?? 0, 0, ',', '.');

        $count = 0;

        // Send to staff_accounting
        $count += static::sendToRole("staff_accounting", self::TYPE_PENDING_APPROVAL, [
            "title" => "Pembayaran Baru Menunggu Approval",
            "message" => "Pengiriman {$noPengiriman} (PO: {$poNumber}) dengan total Rp {$amount} menunggu approval pembayaran",
            "icon" => "money-check-alt",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/accounting/approval-pembayaran",
            "approval_id" => $approval->id,
            "pengiriman_id" => $pengiriman->id,
            "no_pengiriman" => $noPengiriman,
            "amount" => $pengiriman->total_harga_kirim,
        ]);

        // Send to manager_accounting
        $count += static::sendToRole("manager_accounting", self::TYPE_PENDING_APPROVAL, [
            "title" => "Pembayaran Baru Menunggu Approval",
            "message" => "Pengiriman {$noPengiriman} (PO: {$poNumber}) dengan total Rp {$amount} menunggu approval pembayaran",
            "icon" => "money-check-alt",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/accounting/approval-pembayaran",
            "approval_id" => $approval->id,
            "pengiriman_id" => $pengiriman->id,
            "no_pengiriman" => $noPengiriman,
            "amount" => $pengiriman->total_harga_kirim,
        ]);

        return $count;
    }

    /**
     * Notify that an approval pembayaran has been approved.
     *
     * @param ApprovalPembayaran $approval
     * @param User $approvedBy
     * @return int Number of notifications sent
     */
    public static function notifyApproved(ApprovalPembayaran $approval, User $approvedBy): int
    {
        $pengiriman = $approval->pengiriman;

        if (!$pengiriman) {
            return 0;
        }

        $noPengiriman = $pengiriman->no_pengiriman ?? '-';
        $poNumber = $pengiriman->purchaseOrder->po_number ?? '-';
        $amount = number_format($approval->amount_after_refraksi > 0
            ? $approval->amount_after_refraksi
            : $pengiriman->total_harga_kirim ?? 0, 0, ',', '.');

        $count = 0;

        // Notify purchasing team who created the pengiriman
        $picPurchasing = $pengiriman->purchasing;
        if ($picPurchasing && $picPurchasing->id !== $approvedBy->id) {
            $result = static::send($picPurchasing, self::TYPE_APPROVED, [
                "title" => "Pembayaran Disetujui",
                "message" => "Pembayaran untuk {$noPengiriman} (Rp {$amount}) telah disetujui oleh {$approvedBy->nama}",
                "icon" => "check-circle",
                "icon_bg" => "bg-green-100",
                "icon_color" => "text-green-600",
                "url" => "/purchasing/pengiriman",
                "approval_id" => $approval->id,
                "pengiriman_id" => $pengiriman->id,
                "no_pengiriman" => $noPengiriman,
            ]);

            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notify that an approval pembayaran has been rejected.
     *
     * @param ApprovalPembayaran $approval
     * @param User $rejectedBy
     * @param string|null $reason
     * @return int Number of notifications sent
     */
    public static function notifyRejected(
        ApprovalPembayaran $approval,
        User $rejectedBy,
        ?string $reason = null
    ): int {
        $pengiriman = $approval->pengiriman;

        if (!$pengiriman) {
            return 0;
        }

        $noPengiriman = $pengiriman->no_pengiriman ?? '-';
        $reasonText = $reason ? ": {$reason}" : "";

        $count = 0;

        // Notify purchasing team who created the pengiriman
        $picPurchasing = $pengiriman->purchasing;
        if ($picPurchasing && $picPurchasing->id !== $rejectedBy->id) {
            $result = static::send($picPurchasing, self::TYPE_REJECTED, [
                "title" => "Pembayaran Ditolak",
                "message" => "Pembayaran untuk {$noPengiriman} ditolak oleh {$rejectedBy->nama}{$reasonText}",
                "icon" => "times-circle",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
                "url" => "/purchasing/pengiriman",
                "approval_id" => $approval->id,
                "pengiriman_id" => $pengiriman->id,
                "no_pengiriman" => $noPengiriman,
                "reason" => $reason,
            ]);

            if ($result) {
                $count++;
            }
        }

        return $count;
    }
}
