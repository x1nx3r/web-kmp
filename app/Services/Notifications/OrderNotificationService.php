<?php

namespace App\Services\Notifications;

use App\Models\Order;
use App\Models\Pengiriman;
use App\Models\User;

/**
 * Notification service for Order related notifications.
 *
 * Handles notifications for:
 * - Order nearing fulfillment (95-105%) - notifies order creator
 * - Direktur consultation requests - notifies all direktur
 * - Order status changes (future expansion)
 */
class OrderNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_NEARING_FULFILLMENT = "order_nearing_fulfillment";
    public const TYPE_DIREKTUR_CONSULTATION = "order_direktur_consultation";
    public const TYPE_COMPLETED = "order_completed";
    public const TYPE_CANCELLED = "order_cancelled";

    /**
     * Fulfillment threshold percentages
     */
    public const FULFILLMENT_THRESHOLD_MIN = 95;
    public const FULFILLMENT_THRESHOLD_MAX = 105;

    /**
     * Notify order creator that their order is nearing fulfillment (95-105%).
     *
     * @param Order $order
     * @param float $fulfillmentPercentage
     * @param Pengiriman|null $pengiriman The pengiriman that triggered this notification
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyNearingFulfillment(
        Order $order,
        float $fulfillmentPercentage,
        ?Pengiriman $pengiriman = null
    ): ?string {
        $creator = $order->creator;
        if (!$creator) {
            return null;
        }

        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $shippedQty = $order->getShippedQty();
        $totalQty = $order->total_qty;
        $remainingQty = max(0, $totalQty - $shippedQty);

        // Determine message styling based on fulfillment percentage
        $styling = self::getFulfillmentStyling($fulfillmentPercentage);

        return static::send($creator, self::TYPE_NEARING_FULFILLMENT, [
            "title" => "Order {$styling['status_text']} ({$fulfillmentPercentage}%)",
            "message" => "Order {$poNumber} untuk {$klienName} sudah terkirim {$shippedQty} dari {$totalQty} (sisa: {$remainingQty}). Apakah order ini sudah selesai?",
            "icon" => "shipping-fast",
            "icon_bg" => $styling['icon_bg'],
            "icon_color" => $styling['icon_color'],
            "url" => "/orders/{$order->id}",
            "order_id" => $order->id,
            "no_order" => $order->no_order,
            "po_number" => $order->po_number,
            "fulfillment_percentage" => $fulfillmentPercentage,
            "shipped_qty" => $shippedQty,
            "total_qty" => $totalQty,
            "remaining_qty" => $remainingQty,
            "pengiriman_id" => $pengiriman?->id,
        ]);
    }

    /**
     * Send consultation request to Direktur about an order.
     *
     * @param Order $order
     * @param User $requestedBy The marketing user requesting consultation
     * @param string|null $note Optional note from marketing
     * @return int Number of notifications sent
     */
    public static function notifyDirekturConsultation(
        Order $order,
        User $requestedBy,
        ?string $note = null
    ): int {
        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $fulfillmentPercentage = $order->getFulfillmentPercentage();
        $shippedQty = $order->getShippedQty();
        $totalQty = $order->total_qty;

        $message = "{$requestedBy->nama} meminta konsultasi untuk order {$poNumber} ({$klienName}). Fulfillment: {$fulfillmentPercentage}% ({$shippedQty}/{$totalQty}).";

        if ($note) {
            $message .= " Catatan: {$note}";
        }

        return static::sendToRole("direktur", self::TYPE_DIREKTUR_CONSULTATION, [
            "title" => "Konsultasi Order: {$poNumber}",
            "message" => $message,
            "icon" => "question-circle",
            "icon_bg" => "bg-blue-100",
            "icon_color" => "text-blue-600",
            "url" => "/orders/{$order->id}",
            "order_id" => $order->id,
            "no_order" => $order->no_order,
            "po_number" => $order->po_number,
            "fulfillment_percentage" => $fulfillmentPercentage,
            "requested_by_id" => $requestedBy->id,
            "requested_by_name" => $requestedBy->nama,
            "note" => $note,
        ]);
    }

    /**
     * Check if an order is within the fulfillment notification threshold.
     *
     * @param Order $order
     * @return bool
     */
    public static function isWithinFulfillmentThreshold(Order $order): bool
    {
        $percentage = $order->getFulfillmentPercentage();
        return $percentage >= self::FULFILLMENT_THRESHOLD_MIN
            && $percentage <= self::FULFILLMENT_THRESHOLD_MAX;
    }

    /**
     * Get styling configuration based on fulfillment percentage.
     *
     * @param float $percentage
     * @return array{status_text: string, icon_bg: string, icon_color: string}
     */
    protected static function getFulfillmentStyling(float $percentage): array
    {
        if ($percentage > 100) {
            return [
                'status_text' => 'melebihi target',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
            ];
        }

        if ($percentage >= 100) {
            return [
                'status_text' => 'mencapai target',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
            ];
        }

        return [
            'status_text' => 'mendekati target',
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-600',
        ];
    }

    /**
     * Notify order creator that their order has been completed.
     *
     * @param Order $order
     * @param User|null $completedBy The user who marked the order as complete
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyCompleted(Order $order, ?User $completedBy = null): ?string
    {
        $creator = $order->creator;
        if (!$creator) {
            return null;
        }

        // Don't notify if the creator completed it themselves
        if ($completedBy && $completedBy->id === $creator->id) {
            return null;
        }

        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $completedByName = $completedBy ? $completedBy->nama : "System";

        return static::send($creator, self::TYPE_COMPLETED, [
            "title" => "Order Selesai",
            "message" => "Order {$poNumber} untuk {$klienName} telah diselesaikan oleh {$completedByName}",
            "icon" => "check-double",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/orders/{$order->id}",
            "order_id" => $order->id,
            "no_order" => $order->no_order,
            "po_number" => $order->po_number,
            "completed_by_id" => $completedBy?->id,
            "completed_by_name" => $completedByName,
        ]);
    }

    /**
     * Notify order creator that their order has been cancelled.
     *
     * @param Order $order
     * @param User|null $cancelledBy The user who cancelled the order
     * @param string|null $reason The cancellation reason
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyCancelled(
        Order $order,
        ?User $cancelledBy = null,
        ?string $reason = null
    ): ?string {
        $creator = $order->creator;
        if (!$creator) {
            return null;
        }

        // Don't notify if the creator cancelled it themselves
        if ($cancelledBy && $cancelledBy->id === $creator->id) {
            return null;
        }

        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $cancelledByName = $cancelledBy ? $cancelledBy->nama : "System";

        $message = "Order {$poNumber} untuk {$klienName} telah dibatalkan oleh {$cancelledByName}";
        if ($reason) {
            $message .= ". Alasan: {$reason}";
        }

        return static::send($creator, self::TYPE_CANCELLED, [
            "title" => "Order Dibatalkan",
            "message" => $message,
            "icon" => "times-circle",
            "icon_bg" => "bg-red-100",
            "icon_color" => "text-red-600",
            "url" => "/orders/{$order->id}",
            "order_id" => $order->id,
            "no_order" => $order->no_order,
            "po_number" => $order->po_number,
            "cancelled_by_id" => $cancelledBy?->id,
            "cancelled_by_name" => $cancelledByName,
            "reason" => $reason,
        ]);
    }
}
