<?php

namespace App\Services\Notifications;

use App\Models\Forecast;
use App\Models\User;

/**
 * Notification service untuk Forecasting notifications.
 *
 * Handles notifications for:
 * - Forecasting pending reminder (every Monday 07:00 WIB)
 * - Forecasting status updates
 */
class ForecastNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_PENDING_REMINDER = "forecast_pending_reminder";
    public const TYPE_CREATED = "forecast_created";
    public const TYPE_COMPLETED = "forecast_completed";
    public const TYPE_FAILED = "forecast_failed";

    /**
     * Notify Manager & Staff Purchasing tentang forecast pending yang siap dijadikan pengiriman masuk.
     * Dipanggil setiap Senin jam 07:00 WIB via scheduler.
     *
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyPendingForecastReminder(): int
    {
        // Hitung jumlah forecast pending
        $pendingCount = Forecast::pending()->count();
        
        if ($pendingCount === 0) {
            return 0;
        }

        // Ambil beberapa forecast pending untuk ditampilkan di pesan
        $forecasts = Forecast::pending()
            ->with(['purchasing', 'purchaseOrder'])
            ->orderBy('tanggal_forecast', 'asc')
            ->limit(5)
            ->get();

        // Format pesan dengan detail forecast
        $forecastList = $forecasts->map(function($forecast) {
            return "• {$forecast->no_forecast} ({$forecast->tanggal_forecast->format('d M Y')})";
        })->implode("\n");

        $message = "Ada {$pendingCount} forecasting dengan status pending yang siap dijadikan pengiriman masuk.";
        
        if ($forecasts->count() > 0) {
            $message .= "\n\nBeberapa forecast:\n{$forecastList}";
        }

        // Kirim ke Manager Purchasing
        $managerCount = static::sendToRole("manager_purchasing", self::TYPE_PENDING_REMINDER, [
            "title" => "📋 Reminder: Forecast Pending",
            "message" => $message,
            "icon" => "clipboard-list",
            "icon_bg" => "bg-blue-100",
            "icon_color" => "text-blue-600",
            "url" => "/procurement/forecasting?tab=pending",
            "pending_count" => $pendingCount,
            "reminder_type" => "weekly_monday",
        ]);

        // Kirim ke Staff Purchasing
        $staffCount = static::sendToRole("staff_purchasing", self::TYPE_PENDING_REMINDER, [
            "title" => "📋 Reminder: Forecast Pending",
            "message" => $message,
            "icon" => "clipboard-list",
            "icon_bg" => "bg-blue-100",
            "icon_color" => "text-blue-600",
            "url" => "/procurement/forecasting?tab=pending",
            "pending_count" => $pendingCount,
            "reminder_type" => "weekly_monday",
        ]);

        return $managerCount + $staffCount;
    }

    /**
     * Notify Manager Purchasing tentang forecast baru yang dibuat.
     *
     * @param Forecast $forecast
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyCreated(Forecast $forecast): int
    {
        $picName = $forecast->purchasing->nama ?? 'PIC Purchasing';
        $poNumber = $forecast->purchaseOrder->po_number ?? $forecast->purchaseOrder->no_order ?? '-';

        return static::sendToRole("manager_purchasing", self::TYPE_CREATED, [
            "title" => "Forecast Baru Dibuat",
            "message" => "{$forecast->no_forecast} dibuat oleh {$picName} untuk PO: {$poNumber}",
            "icon" => "plus-circle",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/procurement/forecasting",
            "forecast_id" => $forecast->id,
            "no_forecast" => $forecast->no_forecast,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa forecastnya sudah diselesaikan/dikonversi.
     *
     * @param Forecast $forecast
     * @param User|null $completedBy
     * @return string|null Notification ID atau null jika gagal
     */
    public static function notifyCompleted(Forecast $forecast, ?User $completedBy = null): ?string
    {
        $pic = $forecast->purchasing;
        
        if (!$pic) {
            return null;
        }

        $completedByName = $completedBy ? $completedBy->nama : 'System';

        return static::send($pic, self::TYPE_COMPLETED, [
            "title" => "Forecast Diselesaikan",
            "message" => "{$forecast->no_forecast} telah dikonversi menjadi pengiriman oleh {$completedByName}",
            "icon" => "check-circle",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/procurement/forecasting",
            "forecast_id" => $forecast->id,
            "no_forecast" => $forecast->no_forecast,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa forecastnya gagal/bermasalah.
     *
     * @param Forecast $forecast
     * @param string $reason
     * @return string|null Notification ID atau null jika gagal
     */
    public static function notifyFailed(Forecast $forecast, string $reason = ''): ?string
    {
        $pic = $forecast->purchasing;
        
        if (!$pic) {
            return null;
        }

        $message = "{$forecast->no_forecast} gagal diproses";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return static::send($pic, self::TYPE_FAILED, [
            "title" => "Forecast Gagal",
            "message" => $message,
            "icon" => "times-circle",
            "icon_bg" => "bg-red-100",
            "icon_color" => "text-red-600",
            "url" => "/procurement/forecasting",
            "forecast_id" => $forecast->id,
            "no_forecast" => $forecast->no_forecast,
            "reason" => $reason,
        ]);
    }
}
