<?php

namespace App\Services\Notifications;

use App\Models\Pengiriman;
use App\Models\User;

/**
 * Notification service untuk Pengiriman notifications.
 *
 * Handles notifications for:
 * - Pengiriman pending reminder (daily 06:00 WIB)
 * - Pengiriman submitted for verification
 * - Pengiriman verified
 * - Pengiriman revision requested
 */
class PengirimanNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_PENDING_REMINDER = "pengiriman_pending_reminder";
    public const TYPE_SUBMITTED = "pengiriman_submitted";
    public const TYPE_VERIFIED = "pengiriman_verified";
    public const TYPE_REVISION_REQUESTED = "pengiriman_revision_requested";
    public const TYPE_SUCCESS_READY_FOR_REVIEW = "pengiriman_success_ready_for_review";

    /**
     * Notify Manager & Staff Purchasing tentang pengiriman yang belum terselesaikan.
     * Status: pending dan menunggu_verifikasi
     * Dipanggil setiap hari jam 06:00 WIB via scheduler.
     *
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyPendingDeliveries(): int
    {
        return static::notifyPendingPengirimanReminder();
    }

    /**
     * Notify Manager & Staff Purchasing tentang pengiriman yang belum terselesaikan.
     * Status: pending dan menunggu_verifikasi
     * Dipanggil setiap hari jam 06:00 WIB via scheduler.
     *
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyPendingPengirimanReminder(): int
    {
        // Hitung pengiriman pending dan menunggu verifikasi
        $pendingCount = Pengiriman::whereIn('status', ['pending', 'menunggu_verifikasi'])->count();
        
        if ($pendingCount === 0) {
            return 0;
        }

        // Ambil beberapa pengiriman untuk ditampilkan di pesan
        $pengirimans = Pengiriman::whereIn('status', ['pending', 'menunggu_verifikasi'])
            ->with(['purchasing', 'order'])
            ->orderBy('tanggal_kirim', 'asc')
            ->limit(5)
            ->get();

        // Hitung per status
        $pendingOnly = Pengiriman::where('status', 'pending')->count();
        $menungguVerifikasi = Pengiriman::where('status', 'menunggu_verifikasi')->count();

        // Format pesan dengan detail pengiriman
        $pengirimanList = $pengirimans->map(function($pengiriman) {
            $status = $pengiriman->status === 'pending' ? 'Pending' : 'Menunggu Verifikasi';
            $tanggal = $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-';
            return "• {$pengiriman->no_pengiriman} - {$status} ({$tanggal})";
        })->implode("\n");

        $message = "Ada {$pendingCount} pengiriman yang belum terselesaikan.\n";
        $message .= "📋 Pending: {$pendingOnly} | ⏳ Menunggu Verifikasi: {$menungguVerifikasi}";
        
        if ($pengirimans->count() > 0) {
            $message .= "\n\nBeberapa pengiriman:\n{$pengirimanList}";
        }

        // Kirim ke Manager Purchasing
        $managerCount = static::sendToRole("manager_purchasing", self::TYPE_PENDING_REMINDER, [
            "title" => "🚚 Reminder: Pengiriman Belum Terselesaikan",
            "message" => $message,
            "icon" => "truck",
            "icon_bg" => "bg-orange-100",
            "icon_color" => "text-orange-600",
            "url" => "/procurement/pengiriman",
            "pending_count" => $pendingCount,
            "pending_only" => $pendingOnly,
            "menunggu_verifikasi" => $menungguVerifikasi,
            "reminder_type" => "daily",
        ]);

        // Kirim ke Staff Purchasing
        $staffCount = static::sendToRole("staff_purchasing", self::TYPE_PENDING_REMINDER, [
            "title" => "🚚 Reminder: Pengiriman Belum Terselesaikan",
            "message" => $message,
            "icon" => "truck",
            "icon_bg" => "bg-orange-100",
            "icon_color" => "text-orange-600",
            "url" => "/procurement/pengiriman",
            "pending_count" => $pendingCount,
            "pending_only" => $pendingOnly,
            "menunggu_verifikasi" => $menungguVerifikasi,
            "reminder_type" => "daily",
        ]);

        return $managerCount + $staffCount;
    }

    /**
     * Notify Manager Purchasing bahwa ada pengiriman baru menunggu verifikasi.
     *
     * @param Pengiriman $pengiriman
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifySubmittedForVerification(Pengiriman $pengiriman): int
    {
        $picName = $pengiriman->purchasing->nama ?? 'PIC Purchasing';
        $poNumber = $pengiriman->order->po_number ?? $pengiriman->order->no_order ?? '-';

        return static::sendToRole("manager_purchasing", self::TYPE_SUBMITTED, [
            "title" => "Pengiriman Menunggu Verifikasi",
            "message" => "{$pengiriman->no_pengiriman} dari {$picName} (PO: {$poNumber}) menunggu verifikasi Anda",
            "icon" => "clock",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/procurement/pengiriman",
            "pengiriman_id" => $pengiriman->id,
            "no_pengiriman" => $pengiriman->no_pengiriman,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa pengirimannya sudah diverifikasi.
     *
     * @param Pengiriman $pengiriman
     * @param User $verifiedBy
     * @return string|null Notification ID atau null jika gagal
     */
    public static function notifyVerified(Pengiriman $pengiriman, User $verifiedBy): ?string
    {
        $pic = $pengiriman->purchasing;
        
        if (!$pic) {
            return null;
        }

        return static::send($pic, self::TYPE_VERIFIED, [
            "title" => "Pengiriman Diverifikasi",
            "message" => "{$pengiriman->no_pengiriman} telah diverifikasi oleh {$verifiedBy->nama}",
            "icon" => "check-circle",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/procurement/pengiriman",
            "pengiriman_id" => $pengiriman->id,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa pengirimannya perlu revisi.
     *
     * @param Pengiriman $pengiriman
     * @param User $requestedBy
     * @param string $reason
     * @return string|null Notification ID atau null jika gagal
     */
    public static function notifyRevisionRequested(
        Pengiriman $pengiriman,
        User $requestedBy,
        string $reason
    ): ?string {
        $pic = $pengiriman->purchasing;
        
        if (!$pic) {
            return null;
        }

        return static::send($pic, self::TYPE_REVISION_REQUESTED, [
            "title" => "Pengiriman Perlu Revisi",
            "message" => "{$pengiriman->no_pengiriman} perlu direvisi: {$reason}",
            "icon" => "edit",
            "icon_bg" => "bg-orange-100",
            "icon_color" => "text-orange-600",
            "url" => "/procurement/pengiriman",
            "pengiriman_id" => $pengiriman->id,
            "reason" => $reason,
        ]);
    }

    /**
     * Notify Marketing bahwa pengiriman berhasil dan siap untuk dinilai.
     * Dipanggil ketika status pengiriman berubah menjadi 'berhasil'.
     *
     * @param Pengiriman $pengiriman
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifySuccessReadyForReview(Pengiriman $pengiriman): int
    {
        // Cari order
        $order = $pengiriman->order;
        
        if (!$order) {
            return 0;
        }

        $poNumber = $order->po_number ?? $order->no_order ?? '-';
        $klienName = $order->klien->nama ?? 'Klien';

        // Kirim ke semua Marketing
        return static::sendToRole("marketing", self::TYPE_SUCCESS_READY_FOR_REVIEW, [
            "title" => "⭐ Pengiriman Berhasil - Siap Dinilai",
            "message" => "Pengiriman {$pengiriman->no_pengiriman} untuk order {$poNumber} ({$klienName}) telah berhasil dan siap untuk dinilai",
            "icon" => "star",
            "icon_bg" => "bg-green-100",
            "icon_color" => "text-green-600",
            "url" => "/orders/{$order->id}",
            "pengiriman_id" => $pengiriman->id,
            "no_pengiriman" => $pengiriman->no_pengiriman,
            "order_id" => $order->id,
            "po_number" => $poNumber,
        ]);
    }

    /**
     * Notify semua Marketing tentang pengiriman yang berhasil dan belum dinilai.
     * Dipanggil setiap hari jam 06:00 WIB via scheduler.
     *
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyUnreviewedSuccessfulDeliveries(): int
    {
        // Hitung pengiriman berhasil yang belum dinilai
        $unreviewedCount = Pengiriman::where('status', 'berhasil')
            ->whereNull('rating')
            ->count();
        
        if ($unreviewedCount === 0) {
            return 0;
        }

        // Ambil beberapa pengiriman untuk ditampilkan di pesan
        $pengirimans = Pengiriman::where('status', 'berhasil')
            ->whereNull('rating')
            ->with(['order.klien', 'order.creator'])
            ->orderBy('tanggal_kirim', 'asc')
            ->limit(5)
            ->get();

        // Format pesan dengan detail pengiriman
        $pengirimanList = $pengirimans->map(function($pengiriman) {
            $poNumber = $pengiriman->order->po_number ?? $pengiriman->order->no_order ?? '-';
            $klien = $pengiriman->order->klien->nama ?? '-';
            $tanggal = $pengiriman->tanggal_kirim ? $pengiriman->tanggal_kirim->format('d M Y') : '-';
            return "• {$pengiriman->no_pengiriman} - PO: {$poNumber} ({$klien}) - {$tanggal}";
        })->implode("\n");

        $message = "Ada {$unreviewedCount} pengiriman berhasil yang belum dinilai.";
        
        if ($pengirimans->count() > 0) {
            $message .= "\n\nBeberapa pengiriman:\n{$pengirimanList}";
        }

        // Kirim ke semua Marketing
        return static::sendToRole("marketing", self::TYPE_SUCCESS_READY_FOR_REVIEW, [
            "title" => "⭐ Reminder: Pengiriman Siap Dinilai",
            "message" => $message,
            "icon" => "star",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/orders",
            "unreviewed_count" => $unreviewedCount,
            "reminder_type" => "daily",
        ]);
    }
}
