<?php

namespace App\Services\Notifications;

use App\Models\Order;
use App\Models\OrderConsultation;
use App\Models\Pengiriman;
use App\Models\User;

/**
 * Notification service for Order related notifications.
 *
 * Handles notifications for:
 * - Order nearing fulfillment (95-105%) - notifies order creator
 * - Direktur consultation requests - notifies all direktur
 * - Order priority escalated (automatic) - notifies marketing team
 * - Order status changes (future expansion)
 */
class OrderNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_NEARING_FULFILLMENT = "order_nearing_fulfillment";
    public const TYPE_DIREKTUR_CONSULTATION = "order_direktur_consultation";
    public const TYPE_CONSULTATION_RESPONDED = "order_consultation_responded";
    public const TYPE_COMPLETED = "order_completed";
    public const TYPE_CANCELLED = "order_cancelled";
    public const TYPE_PRIORITY_ESCALATED = "order_priority_escalated";

    /**
     * Priority levels in order of urgency (highest to lowest)
     */
    public const PRIORITY_LEVELS = [
        "mendesak" => 4,
        "tinggi" => 3,
        "normal" => 2,
        "rendah" => 1,
    ];

    /**
     * Priority labels for display
     */
    public const PRIORITY_LABELS = [
        "mendesak" => "Mendesak",
        "tinggi" => "Tinggi",
        "normal" => "Normal",
        "rendah" => "Rendah",
    ];

    /**
     * Fulfillment threshold percentages
     */
    public const FULFILLMENT_THRESHOLD_MIN = 95;
    public const FULFILLMENT_THRESHOLD_MAX = 105;

    /**
     * Notify all Marketing users that an order is nearing fulfillment (95-105%).
     *
     * @param Order $order
     * @param float $fulfillmentPercentage
     * @param Pengiriman|null $pengiriman The pengiriman that triggered this notification
     * @return int Number of notifications sent
     */
    public static function notifyNearingFulfillment(
        Order $order,
        float $fulfillmentPercentage,
        ?Pengiriman $pengiriman = null,
    ): int {
        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $shippedQty = $order->getShippedQty();
        $totalQty = $order->total_qty;
        $remainingQty = max(0, $totalQty - $shippedQty);

        // Determine message styling based on fulfillment percentage
        $styling = self::getFulfillmentStyling($fulfillmentPercentage);

        return static::sendToRole("marketing", self::TYPE_NEARING_FULFILLMENT, [
            "title" => "Order {$styling["status_text"]} ({$fulfillmentPercentage}%)",
            "message" => "Order {$poNumber} untuk {$klienName} sudah terkirim {$shippedQty} dari {$totalQty} (sisa: {$remainingQty}). Apakah order ini sudah selesai?",
            "icon" => "shipping-fast",
            "icon_bg" => $styling["icon_bg"],
            "icon_color" => $styling["icon_color"],
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
        ?string $note = null,
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

        return static::sendToRole(
            "direktur",
            self::TYPE_DIREKTUR_CONSULTATION,
            [
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
            ],
        );
    }

    /**
     * Notify all Marketing users that a Direktur has responded to a consultation.
     *
     * @param OrderConsultation $consultation
     * @return int Number of notifications sent
     */
    public static function notifyConsultationResponded(
        OrderConsultation $consultation,
    ): int {
        $order = $consultation->order;
        $responder = $consultation->responder;
        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $responderName = $responder ? $responder->nama : "Direktur";

        $responseLabel =
            $consultation->response_type === "selesai"
                ? "menyarankan untuk menutup"
                : "menyarankan untuk melanjutkan";

        $message = "{$responderName} {$responseLabel} order {$poNumber} ({$klienName}).";

        if ($consultation->response_note) {
            $message .= " Catatan: {$consultation->response_note}";
        }

        $iconConfig =
            $consultation->response_type === "selesai"
                ? [
                    "icon" => "check-circle",
                    "icon_bg" => "bg-green-100",
                    "icon_color" => "text-green-600",
                ]
                : [
                    "icon" => "arrow-right",
                    "icon_bg" => "bg-blue-100",
                    "icon_color" => "text-blue-600",
                ];

        return static::sendToRole(
            "marketing",
            self::TYPE_CONSULTATION_RESPONDED,
            [
                "title" => "Respons Direktur: {$poNumber}",
                "message" => $message,
                "icon" => $iconConfig["icon"],
                "icon_bg" => $iconConfig["icon_bg"],
                "icon_color" => $iconConfig["icon_color"],
                "url" => "/orders/{$order->id}",
                "order_id" => $order->id,
                "no_order" => $order->no_order,
                "po_number" => $order->po_number,
                "consultation_id" => $consultation->id,
                "response_type" => $consultation->response_type,
                "response_note" => $consultation->response_note,
                "responded_by_id" => $responder?->id,
                "responded_by_name" => $responderName,
            ],
        );
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
        return $percentage >= self::FULFILLMENT_THRESHOLD_MIN &&
            $percentage <= self::FULFILLMENT_THRESHOLD_MAX;
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
                "status_text" => "melebihi target",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
            ];
        }

        if ($percentage >= 100) {
            return [
                "status_text" => "mencapai target",
                "icon_bg" => "bg-green-100",
                "icon_color" => "text-green-600",
            ];
        }

        return [
            "status_text" => "mendekati target",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
        ];
    }

    /**
     * Notify order creator that their order has been completed.
     *
     * @param Order $order
     * @param User|null $completedBy The user who marked the order as complete
     * @return string|null The notification ID or null if no creator
     */
    public static function notifyCompleted(
        Order $order,
        ?User $completedBy = null,
    ): ?string {
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
        ?string $reason = null,
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

    /**
     * Notify marketing team when order priority is escalated.
     *
     * @param Order $order
     * @param string $oldPriority Previous priority level
     * @param string $newPriority New priority level
     * @param User|null $changedBy User who made the change (null if system)
     * @return int Number of notifications sent
     */
    public static function notifyPriorityEscalated(
        Order $order,
        string $oldPriority,
        string $newPriority,
        ?User $changedBy = null,
        ?int $daysRemaining = null,
    ): int {
        // Only notify if priority actually increased
        $oldLevel = self::PRIORITY_LEVELS[$oldPriority] ?? 0;
        $newLevel = self::PRIORITY_LEVELS[$newPriority] ?? 0;

        if ($newLevel <= $oldLevel) {
            return 0;
        }

        // Only notify for escalation to tinggi or mendesak
        if (!in_array($newPriority, ["tinggi", "mendesak"])) {
            return 0;
        }

        $poNumber = $order->po_number ?? $order->no_order;
        $klienName = $order->klien ? $order->klien->nama : "Klien";
        $oldLabel = self::PRIORITY_LABELS[$oldPriority] ?? $oldPriority;
        $newLabel = self::PRIORITY_LABELS[$newPriority] ?? $newPriority;
        $changedByName = $changedBy ? $changedBy->nama : "Sistem";

        $iconConfig = self::getPriorityIconConfig($newPriority);

        // Build message with deadline context if available
        if ($daysRemaining !== null) {
            if ($daysRemaining <= 0) {
                $deadlineText = "sudah melewati deadline";
            } elseif ($daysRemaining === 1) {
                $deadlineText = "deadline besok";
            } else {
                $deadlineText = "{$daysRemaining} hari menuju deadline";
            }
            $message = "Prioritas order {$poNumber} ({$klienName}) otomatis naik dari {$oldLabel} → {$newLabel}. {$deadlineText}.";
        } else {
            $message = "Prioritas order {$poNumber} ({$klienName}) dinaikkan dari {$oldLabel} menjadi {$newLabel} oleh {$changedByName}.";
        }

        $notificationData = [
            "title" => "Prioritas Naik: {$poNumber}",
            "message" => $message,
            "icon" => "arrow-up",
            "icon_bg" => $iconConfig["icon_bg"],
            "icon_color" => $iconConfig["icon_color"],
            "url" => "/orders/{$order->id}",
            "order_id" => $order->id,
            "no_order" => $order->no_order,
            "po_number" => $order->po_number,
            "old_priority" => $oldPriority,
            "new_priority" => $newPriority,
            "old_priority_label" => $oldLabel,
            "new_priority_label" => $newLabel,
            "changed_by_id" => $changedBy?->id,
            "changed_by_name" => $changedByName,
            "days_remaining" => $daysRemaining,
            "is_automatic" => $changedBy === null,
        ];

        // Get marketing users except the one who made the change
        $changedById = $changedBy?->id;
        $marketingUsers = \App\Models\User::where("role", "marketing")
            ->where("status", "aktif")
            ->when($changedById, function ($query) use ($changedById) {
                $query->where("id", "!=", $changedById);
            })
            ->get();

        return static::sendToMany(
            $marketingUsers,
            self::TYPE_PRIORITY_ESCALATED,
            $notificationData,
        );
    }

    /**
     * Get icon configuration based on priority level.
     *
     * @param string $priority
     * @return array{icon: string, icon_bg: string, icon_color: string}
     */
    protected static function getPriorityIconConfig(string $priority): array
    {
        return match ($priority) {
            "mendesak" => [
                "icon" => "exclamation-circle",
                "icon_bg" => "bg-red-100",
                "icon_color" => "text-red-600",
            ],
            "tinggi" => [
                "icon" => "exclamation-triangle",
                "icon_bg" => "bg-orange-100",
                "icon_color" => "text-orange-600",
            ],
            "normal" => [
                "icon" => "clipboard-list",
                "icon_bg" => "bg-blue-100",
                "icon_color" => "text-blue-600",
            ],
            default => [
                "icon" => "clipboard-list",
                "icon_bg" => "bg-gray-100",
                "icon_color" => "text-gray-600",
            ],
        };
    }

    /**
     * Check if a priority change is an escalation.
     *
     * @param string $oldPriority
     * @param string $newPriority
     * @return bool
     */
    public static function isPriorityEscalation(
        string $oldPriority,
        string $newPriority,
    ): bool {
        $oldLevel = self::PRIORITY_LEVELS[$oldPriority] ?? 0;
        $newLevel = self::PRIORITY_LEVELS[$newPriority] ?? 0;

        return $newLevel > $oldLevel;
    }
}
