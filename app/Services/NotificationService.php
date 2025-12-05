<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderConsultation;
use App\Models\Penawaran;
use App\Models\Pengiriman;
use App\Models\User;
use App\Services\Notifications\BaseNotificationService;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Notifications\PenawaranNotificationService;
use Illuminate\Support\Collection;

/**
 * Main NotificationService facade.
 *
 * This class provides a unified interface to all notification services
 * and maintains backwards compatibility with existing code.
 *
 * For new code, consider using the specific notification services directly:
 * - BaseNotificationService: Core notification operations (send, read, manage)
 * - PenawaranNotificationService: Penawaran/quotation notifications
 * - OrderNotificationService: Order fulfillment notifications
 */
class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Notification Type Constants (for backwards compatibility)
    |--------------------------------------------------------------------------
    */

    // Penawaran types
    public const TYPE_PENAWARAN_SUBMITTED = PenawaranNotificationService::TYPE_SUBMITTED;
    public const TYPE_PENAWARAN_APPROVED = PenawaranNotificationService::TYPE_APPROVED;
    public const TYPE_PENAWARAN_REJECTED = PenawaranNotificationService::TYPE_REJECTED;

    // Order types
    public const TYPE_ORDER_NEARING_FULFILLMENT = OrderNotificationService::TYPE_NEARING_FULFILLMENT;
    public const TYPE_ORDER_DIREKTUR_CONSULTATION = OrderNotificationService::TYPE_DIREKTUR_CONSULTATION;
    public const TYPE_ORDER_CONSULTATION_RESPONDED = OrderNotificationService::TYPE_CONSULTATION_RESPONDED;
    public const TYPE_ORDER_PRIORITY_ESCALATED = OrderNotificationService::TYPE_PRIORITY_ESCALATED;

    /*
    |--------------------------------------------------------------------------
    | Core Notification Methods (delegated to BaseNotificationService)
    |--------------------------------------------------------------------------
    */

    /**
     * Send a notification to a user.
     */
    public static function send(User $user, string $type, array $data): ?string
    {
        return BaseNotificationService::send($user, $type, $data);
    }

    /**
     * Send a notification to multiple users.
     */
    public static function sendToMany($users, string $type, array $data): int
    {
        return BaseNotificationService::sendToMany($users, $type, $data);
    }

    /**
     * Send notification to all users with a specific role.
     */
    public static function sendToRole(
        string $role,
        string $type,
        array $data,
    ): int {
        return BaseNotificationService::sendToRole($role, $type, $data);
    }

    /**
     * Get unread notifications count for a user.
     */
    public static function getUnreadCount(User $user): int
    {
        return BaseNotificationService::getUnreadCount($user);
    }

    /**
     * Get notifications for a user.
     */
    public static function getNotifications(
        User $user,
        int $limit = 10,
        bool $unreadOnly = false,
    ): Collection {
        return BaseNotificationService::getNotifications(
            $user,
            $limit,
            $unreadOnly,
        );
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead(string $notificationId, User $user): bool
    {
        return BaseNotificationService::markAsRead($notificationId, $user);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(User $user): int
    {
        return BaseNotificationService::markAllAsRead($user);
    }

    /**
     * Delete old read notifications (cleanup).
     */
    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        return BaseNotificationService::cleanupOldNotifications($daysOld);
    }

    /*
    |--------------------------------------------------------------------------
    | Penawaran Notifications (delegated to PenawaranNotificationService)
    |--------------------------------------------------------------------------
    */

    /**
     * Notify all direktur about a new penawaran submission.
     */
    public static function notifyPenawaranSubmitted(Penawaran $penawaran): int
    {
        return PenawaranNotificationService::notifySubmitted($penawaran);
    }

    /**
     * Notify the creator that their penawaran was approved.
     */
    public static function notifyPenawaranApproved(
        Penawaran $penawaran,
    ): ?string {
        return PenawaranNotificationService::notifyApproved($penawaran);
    }

    /**
     * Notify the creator that their penawaran was rejected.
     */
    public static function notifyPenawaranRejected(
        Penawaran $penawaran,
        string $reason,
    ): ?string {
        return PenawaranNotificationService::notifyRejected(
            $penawaran,
            $reason,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Order Notifications (delegated to OrderNotificationService)
    |--------------------------------------------------------------------------
    */

    /**
     * Notify all Marketing users that an order is nearing fulfillment (95-105%).
     */
    public static function notifyOrderNearingFulfillment(
        Order $order,
        float $fulfillmentPercentage,
        ?Pengiriman $pengiriman = null,
    ): int {
        return OrderNotificationService::notifyNearingFulfillment(
            $order,
            $fulfillmentPercentage,
            $pengiriman,
        );
    }

    /**
     * Send consultation request to Direktur about an order.
     */
    public static function notifyDirekturOrderConsultation(
        Order $order,
        User $requestedBy,
        ?string $note = null,
    ): int {
        return OrderNotificationService::notifyDirekturConsultation(
            $order,
            $requestedBy,
            $note,
        );
    }

    /**
     * Notify all Marketing users that a Direktur has responded to a consultation.
     */
    public static function notifyConsultationResponded(
        OrderConsultation $consultation,
    ): int {
        return OrderNotificationService::notifyConsultationResponded(
            $consultation,
        );
    }

    /**
     * Notify marketing team when order priority is escalated.
     */
    public static function notifyOrderPriorityEscalated(
        Order $order,
        string $oldPriority,
        string $newPriority,
        ?User $changedBy = null,
        ?int $daysRemaining = null,
    ): int {
        return OrderNotificationService::notifyPriorityEscalated(
            $order,
            $oldPriority,
            $newPriority,
            $changedBy,
            $daysRemaining,
        );
    }

    /**
     * Check if a priority change is an escalation.
     */
    public static function isPriorityEscalation(
        string $oldPriority,
        string $newPriority,
    ): bool {
        return OrderNotificationService::isPriorityEscalation(
            $oldPriority,
            $newPriority,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the specific notification service for penawaran.
     */
    public static function penawaran(): string
    {
        return PenawaranNotificationService::class;
    }

    /**
     * Get the specific notification service for orders.
     */
    public static function order(): string
    {
        return OrderNotificationService::class;
    }

    /**
     * Get the base notification service.
     */
    public static function base(): string
    {
        return BaseNotificationService::class;
    }
}
