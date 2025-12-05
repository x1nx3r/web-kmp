<?php

namespace App\Services\Notifications;

use App\Models\CatatanPiutang;
use App\Models\InvoicePenagihan;
use App\Models\User;

/**
 * Notification service untuk Piutang notifications.
 *
 * Handles notifications for:
 * - Piutang supplier yang melebihi jatuh tempo
 * - Piutang pabrik (klien) yang melebihi jatuh tempo
 */
class PiutangNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_SUPPLIER_OVERDUE = "piutang_supplier_overdue";
    public const TYPE_PABRIK_OVERDUE = "piutang_pabrik_overdue";
    public const TYPE_SUPPLIER_NEAR_DUE = "piutang_supplier_near_due";
    public const TYPE_PABRIK_NEAR_DUE = "piutang_pabrik_near_due";

    /**
     * Notify accounting team tentang piutang supplier yang melebihi jatuh tempo.
     *
     * @param CatatanPiutang $piutang
     * @param int $daysOverdue
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifySupplierOverdue(CatatanPiutang $piutang, int $daysOverdue): int
    {
        $supplierName = $piutang->supplier->nama ?? 'Unknown Supplier';
        $amount = number_format($piutang->sisa_piutang, 0, ',', '.');

        $count = 0;

        // Kirim ke staff_accounting dan manager_accounting
        $roles = ['staff_accounting', 'manager_accounting'];

        foreach ($roles as $role) {
            $count += static::sendToRole($role, self::TYPE_SUPPLIER_OVERDUE, [
                "title" => "Piutang Supplier Jatuh Tempo",
                "message" => "Piutang dari {$supplierName} sebesar Rp {$amount} telah melewati jatuh tempo {$daysOverdue} hari",
                "icon" => "exclamation-triangle",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
                "url" => "/accounting/catatan-piutang",
                "piutang_id" => $piutang->id,
                "supplier_id" => $piutang->supplier_id,
                "supplier_name" => $supplierName,
                "sisa_piutang" => $piutang->sisa_piutang,
                "days_overdue" => $daysOverdue,
            ]);
        }

        return $count;
    }

    /**
     * Notify accounting team tentang piutang pabrik yang melebihi jatuh tempo.
     *
     * @param InvoicePenagihan $invoice
     * @param int $daysOverdue
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyPabrikOverdue(InvoicePenagihan $invoice, int $daysOverdue): int
    {
        $customerName = $invoice->customer_name ?? 'Unknown Customer';
        $invoiceNumber = $invoice->invoice_number ?? '-';
        $amount = number_format($invoice->total_amount, 0, ',', '.');

        $count = 0;

        // Kirim ke staff_accounting dan manager_accounting
        $roles = ['staff_accounting', 'manager_accounting'];

        foreach ($roles as $role) {
            $count += static::sendToRole($role, self::TYPE_PABRIK_OVERDUE, [
                "title" => "Invoice Pabrik Jatuh Tempo",
                "message" => "Invoice {$invoiceNumber} untuk {$customerName} sebesar Rp {$amount} telah melewati jatuh tempo {$daysOverdue} hari",
                "icon" => "exclamation-triangle",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
                "url" => "/accounting/catatan-piutang",
                "invoice_id" => $invoice->id,
                "invoice_number" => $invoiceNumber,
                "customer_name" => $customerName,
                "total_amount" => $invoice->total_amount,
                "days_overdue" => $daysOverdue,
            ]);
        }

        return $count;
    }

    /**
     * Notify accounting team tentang piutang supplier yang mendekati jatuh tempo.
     *
     * @param CatatanPiutang $piutang
     * @param int $daysUntilDue
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifySupplierNearDue(CatatanPiutang $piutang, int $daysUntilDue): int
    {
        $supplierName = $piutang->supplier->nama ?? 'Unknown Supplier';
        $amount = number_format($piutang->sisa_piutang, 0, ',', '.');

        $count = 0;

        $roles = ['staff_accounting', 'manager_accounting'];

        foreach ($roles as $role) {
            $count += static::sendToRole($role, self::TYPE_SUPPLIER_NEAR_DUE, [
                "title" => "Piutang Supplier Mendekati Jatuh Tempo",
                "message" => "Piutang dari {$supplierName} sebesar Rp {$amount} akan jatuh tempo dalam {$daysUntilDue} hari",
                "icon" => "clock",
                "icon_bg" => "bg-yellow-100",
                "icon_color" => "text-yellow-600",
                "url" => "/accounting/catatan-piutang",
                "piutang_id" => $piutang->id,
                "supplier_id" => $piutang->supplier_id,
                "supplier_name" => $supplierName,
                "sisa_piutang" => $piutang->sisa_piutang,
                "days_until_due" => $daysUntilDue,
            ]);
        }

        return $count;
    }

    /**
     * Notify accounting team tentang piutang pabrik yang mendekati jatuh tempo.
     *
     * @param InvoicePenagihan $invoice
     * @param int $daysUntilDue
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyPabrikNearDue(InvoicePenagihan $invoice, int $daysUntilDue): int
    {
        $customerName = $invoice->customer_name ?? 'Unknown Customer';
        $invoiceNumber = $invoice->invoice_number ?? '-';
        $amount = number_format($invoice->total_amount, 0, ',', '.');

        $count = 0;

        $roles = ['staff_accounting', 'manager_accounting'];

        foreach ($roles as $role) {
            $count += static::sendToRole($role, self::TYPE_PABRIK_NEAR_DUE, [
                "title" => "Invoice Pabrik Mendekati Jatuh Tempo",
                "message" => "Invoice {$invoiceNumber} untuk {$customerName} sebesar Rp {$amount} akan jatuh tempo dalam {$daysUntilDue} hari",
                "icon" => "clock",
                "icon_bg" => "bg-yellow-100",
                "icon_color" => "text-yellow-600",
                "url" => "/accounting/catatan-piutang",
                "invoice_id" => $invoice->id,
                "invoice_number" => $invoiceNumber,
                "customer_name" => $customerName,
                "total_amount" => $invoice->total_amount,
                "days_until_due" => $daysUntilDue,
            ]);
        }

        return $count;
    }
}
