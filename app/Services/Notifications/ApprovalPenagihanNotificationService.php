<?php

namespace App\Services\Notifications;

use App\Models\ApprovalPenagihan;
use App\Models\User;

/**
 * Notification service for Approval Penagihan notifications.
 *
 * Handles notifications for:
 * - New approval penagihan created (pending) - notifies accounting team
 * - Approval penagihan approved - notifies relevant parties
 * - Approval penagihan rejected - notifies submitter
 */
class ApprovalPenagihanNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_PENDING_APPROVAL = "approval_penagihan_pending";
    public const TYPE_APPROVED = "approval_penagihan_approved";
    public const TYPE_REJECTED = "approval_penagihan_rejected";

    /**
     * Notify Accounting team that there's a new approval penagihan pending.
     *
     * @param ApprovalPenagihan $approval
     * @return int Number of notifications sent
     */
    public static function notifyPendingApproval(ApprovalPenagihan $approval): int
    {
        $pengiriman = $approval->pengiriman;
        $invoice = $approval->invoice;

        if (!$pengiriman || !$invoice) {
            return 0;
        }

        $noPengiriman = $pengiriman->no_pengiriman ?? '-';
        $invoiceNumber = $invoice->invoice_number ?? '-';
        $customerName = $invoice->customer_name ?? '-';
        $amount = number_format($invoice->total_amount ?? 0, 0, ',', '.');

        $count = 0;

        // Send to staff_accounting
        $count += static::sendToRole("staff_accounting", self::TYPE_PENDING_APPROVAL, [
            "title" => "Invoice Penagihan Baru Menunggu Approval",
            "message" => "Invoice {$invoiceNumber} untuk {$customerName} (Rp {$amount}) menunggu approval penagihan",
            "icon" => "file-invoice-dollar",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/accounting/approval-penagihan",
            "approval_id" => $approval->id,
            "invoice_id" => $invoice->id,
            "pengiriman_id" => $pengiriman->id,
            "invoice_number" => $invoiceNumber,
            "amount" => $invoice->total_amount,
        ]);

        // Send to manager_accounting
        $count += static::sendToRole("manager_accounting", self::TYPE_PENDING_APPROVAL, [
            "title" => "Invoice Penagihan Baru Menunggu Approval",
            "message" => "Invoice {$invoiceNumber} untuk {$customerName} (Rp {$amount}) menunggu approval penagihan",
            "icon" => "file-invoice-dollar",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/accounting/approval-penagihan",
            "approval_id" => $approval->id,
            "invoice_id" => $invoice->id,
            "pengiriman_id" => $pengiriman->id,
            "invoice_number" => $invoiceNumber,
            "amount" => $invoice->total_amount,
        ]);

        return $count;
    }

    /**
     * Notify that an approval penagihan has been approved.
     *
     * @param ApprovalPenagihan $approval
     * @param User $approvedBy
     * @return int Number of notifications sent
     */
    public static function notifyApproved(ApprovalPenagihan $approval, User $approvedBy): int
    {
        $pengiriman = $approval->pengiriman;
        $invoice = $approval->invoice;

        if (!$pengiriman || !$invoice) {
            return 0;
        }

        $invoiceNumber = $invoice->invoice_number ?? '-';
        $customerName = $invoice->customer_name ?? '-';
        $amount = number_format($invoice->total_amount ?? 0, 0, ',', '.');

        $count = 0;

        // Notify purchasing team who created the pengiriman
        $picPurchasing = $pengiriman->purchasing;
        if ($picPurchasing && $picPurchasing->id !== $approvedBy->id) {
            $result = static::send($picPurchasing, self::TYPE_APPROVED, [
                "title" => "Invoice Penagihan Disetujui",
                "message" => "Invoice {$invoiceNumber} untuk {$customerName} (Rp {$amount}) telah disetujui oleh {$approvedBy->nama}",
                "icon" => "check-circle",
                "icon_bg" => "bg-green-100",
                "icon_color" => "text-green-600",
                "url" => "/purchasing/pengiriman",
                "approval_id" => $approval->id,
                "invoice_id" => $invoice->id,
                "pengiriman_id" => $pengiriman->id,
                "invoice_number" => $invoiceNumber,
            ]);

            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Notify that an approval penagihan has been rejected.
     *
     * @param ApprovalPenagihan $approval
     * @param User $rejectedBy
     * @param string|null $reason
     * @return int Number of notifications sent
     */
    public static function notifyRejected(
        ApprovalPenagihan $approval,
        User $rejectedBy,
        ?string $reason = null
    ): int {
        $pengiriman = $approval->pengiriman;
        $invoice = $approval->invoice;

        if (!$pengiriman || !$invoice) {
            return 0;
        }

        $invoiceNumber = $invoice->invoice_number ?? '-';
        $reasonText = $reason ? ": {$reason}" : "";

        $count = 0;

        // Notify purchasing team who created the pengiriman
        $picPurchasing = $pengiriman->purchasing;
        if ($picPurchasing && $picPurchasing->id !== $rejectedBy->id) {
            $result = static::send($picPurchasing, self::TYPE_REJECTED, [
                "title" => "Invoice Penagihan Ditolak",
                "message" => "Invoice {$invoiceNumber} ditolak oleh {$rejectedBy->nama}{$reasonText}",
                "icon" => "times-circle",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
                "url" => "/purchasing/pengiriman",
                "approval_id" => $approval->id,
                "invoice_id" => $invoice->id,
                "pengiriman_id" => $pengiriman->id,
                "invoice_number" => $invoiceNumber,
                "reason" => $reason,
            ]);

            if ($result) {
                $count++;
            }
        }

        return $count;
    }
}
