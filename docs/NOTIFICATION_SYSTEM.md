# Notification System Documentation

Dokumentasi lengkap untuk sistem notifikasi in-app di PT. Kamil Maju Persada.

## Daftar Isi

1. [Overview](#overview)
2. [Struktur Folder](#struktur-folder)
3. [Cara Kerja](#cara-kerja)
4. [Membuat Notification Service Baru](#membuat-notification-service-baru)
5. [Contoh Implementasi](#contoh-implementasi)
6. [Best Practices](#best-practices)
7. [Referensi API](#referensi-api)

---

## Overview

Sistem notifikasi menggunakan arsitektur berbasis service dengan pemisahan concern yang jelas:

- **BaseNotificationService**: Operasi dasar (kirim, baca, kelola)
- **Domain-specific Services**: Notifikasi per domain bisnis (Penawaran, Order, dll)
- **NotificationService Facade**: Entry point utama untuk backwards compatibility

### Fitur Utama

- ✅ Notifikasi real-time via polling (15 detik)
- ✅ Bell dropdown dengan badge unread count
- ✅ Halaman notifikasi lengkap dengan filter
- ✅ Mark as read (single/all)
- ✅ Role-based notifications
- ✅ Extensible architecture

---

## Struktur Folder

```
app/Services/
├── NotificationService.php              # Facade utama (backwards compatible)
└── Notifications/
    ├── BaseNotificationService.php      # Core operations
    ├── PenawaranNotificationService.php # Notifikasi penawaran
    └── OrderNotificationService.php     # Notifikasi order
```

### Database Schema

Notifikasi disimpan di tabel `notifications`:

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,           -- UUID
    type VARCHAR(255),                  -- Tipe notifikasi (e.g., "order_nearing_fulfillment")
    notifiable_type VARCHAR(255),       -- Class model (e.g., "App\Models\User")
    notifiable_id BIGINT UNSIGNED,      -- ID user penerima
    data JSON,                          -- Payload notifikasi (title, message, url, dll)
    read_at TIMESTAMP NULL,             -- NULL = unread
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Cara Kerja

### 1. Mengirim Notifikasi

```php
use App\Services\Notifications\OrderNotificationService;

// Kirim notifikasi ke order creator
OrderNotificationService::notifyNearingFulfillment($order, 97.5, $pengiriman);
```

### 2. Data Notifikasi

Setiap notifikasi memiliki struktur data:

```php
[
    "title" => "Order mendekati target (97.5%)",
    "message" => "Order PO-001 untuk PT ABC sudah terkirim 975 dari 1000 (sisa: 25).",
    "icon" => "shipping-fast",           // FontAwesome icon (tanpa prefix fa-)
    "icon_bg" => "bg-yellow-100",        // Tailwind background class
    "icon_color" => "text-yellow-600",   // Tailwind text color class
    "url" => "/orders/123",              // URL saat notifikasi di-klik
    // ... data tambahan sesuai kebutuhan
]
```

### 3. Menampilkan Notifikasi

Notifikasi ditampilkan via:
- **Bell dropdown**: `resources/views/livewire/notification-bell.blade.php`
- **Halaman penuh**: `resources/views/pages/notifications.blade.php`

Polling dilakukan setiap 15 detik via Alpine.js fetch ke `/api/notifications`.

---

## Membuat Notification Service Baru

### Step 1: Buat File Service

Buat file baru di `app/Services/Notifications/`:

```php
<?php

namespace App\Services\Notifications;

use App\Models\YourModel;
use App\Models\User;

/**
 * Notification service untuk [Domain] notifications.
 *
 * Handles notifications for:
 * - [List fitur notifikasi]
 */
class YourDomainNotificationService extends BaseNotificationService
{
    /**
     * Notification type constants
     */
    public const TYPE_SOMETHING_HAPPENED = "yourdomain_something_happened";
    public const TYPE_ANOTHER_EVENT = "yourdomain_another_event";

    /**
     * Notify user tentang sesuatu.
     *
     * @param YourModel $model
     * @return string|null Notification ID atau null jika gagal
     */
    public static function notifySomethingHappened(YourModel $model): ?string
    {
        $recipient = $model->user; // User yang akan menerima notifikasi
        
        if (!$recipient) {
            return null;
        }

        return static::send($recipient, self::TYPE_SOMETHING_HAPPENED, [
            "title" => "Judul Notifikasi",
            "message" => "Pesan detail notifikasi untuk {$model->name}",
            "icon" => "bell",
            "icon_bg" => "bg-blue-100",
            "icon_color" => "text-blue-600",
            "url" => "/your-domain/{$model->id}",
            "model_id" => $model->id,
            // tambahkan data lain yang diperlukan
        ]);
    }

    /**
     * Notify semua user dengan role tertentu.
     *
     * @param YourModel $model
     * @return int Jumlah notifikasi terkirim
     */
    public static function notifyAllManagers(YourModel $model): int
    {
        return static::sendToRole("manager", self::TYPE_ANOTHER_EVENT, [
            "title" => "Event Baru",
            "message" => "Ada event baru yang perlu perhatian Anda",
            "icon" => "exclamation-circle",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/your-domain/{$model->id}",
            "model_id" => $model->id,
        ]);
    }
}
```

### Step 2: (Opsional) Tambahkan ke Facade

Jika ingin akses via facade utama, tambahkan method di `NotificationService.php`:

```php
// Di app/Services/NotificationService.php

use App\Services\Notifications\YourDomainNotificationService;

// Tambahkan constant
public const TYPE_YOURDOMAIN_SOMETHING = YourDomainNotificationService::TYPE_SOMETHING_HAPPENED;

// Tambahkan method wrapper
public static function notifyYourDomainSomething(YourModel $model): ?string
{
    return YourDomainNotificationService::notifySomethingHappened($model);
}
```

### Step 3: Trigger Notifikasi

Panggil notifikasi dari controller, model, atau observer:

```php
// Di Controller
public function approve(Request $request, $id)
{
    $model = YourModel::findOrFail($id);
    $model->approve();
    
    // Kirim notifikasi
    YourDomainNotificationService::notifySomethingHappened($model);
    
    return redirect()->back()->with('success', 'Approved!');
}

// Atau di Model
public function approve(): bool
{
    $this->status = 'approved';
    $saved = $this->save();
    
    if ($saved) {
        YourDomainNotificationService::notifySomethingHappened($this);
    }
    
    return $saved;
}

// Atau di Observer
public function updated(YourModel $model)
{
    if ($model->isDirty('status') && $model->status === 'approved') {
        YourDomainNotificationService::notifySomethingHappened($model);
    }
}
```

---

## Contoh Implementasi

### Contoh 1: Notifikasi Pengiriman (PengirimanNotificationService)

```php
<?php

namespace App\Services\Notifications;

use App\Models\Pengiriman;
use App\Models\User;

class PengirimanNotificationService extends BaseNotificationService
{
    public const TYPE_SUBMITTED = "pengiriman_submitted";
    public const TYPE_VERIFIED = "pengiriman_verified";
    public const TYPE_REVISION_REQUESTED = "pengiriman_revision_requested";

    /**
     * Notify Manager/Direktur bahwa ada pengiriman baru menunggu verifikasi.
     */
    public static function notifySubmittedForVerification(Pengiriman $pengiriman): int
    {
        $picName = $pengiriman->purchasing->nama ?? 'PIC Purchasing';
        $poNumber = $pengiriman->order->po_number ?? $pengiriman->order->no_order ?? '-';

        return static::sendToRole("manager_purchasing", self::TYPE_SUBMITTED, [
            "title" => "Pengiriman Menunggu Verifikasi",
            "message" => "{$pengiriman->no_pengiriman} dari {$picName} (PO: {$poNumber}) menunggu verifikasi Anda",
            "icon" => "truck",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/purchasing/pengiriman",
            "pengiriman_id" => $pengiriman->id,
            "no_pengiriman" => $pengiriman->no_pengiriman,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa pengirimannya sudah diverifikasi.
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
            "url" => "/purchasing/pengiriman",
            "pengiriman_id" => $pengiriman->id,
        ]);
    }

    /**
     * Notify PIC Purchasing bahwa pengirimannya perlu revisi.
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
            "url" => "/purchasing/pengiriman",
            "pengiriman_id" => $pengiriman->id,
            "reason" => $reason,
        ]);
    }
}
```

### Contoh 2: Notifikasi Approval Pembayaran

```php
<?php

namespace App\Services\Notifications;

use App\Models\ApprovalPembayaran;
use App\Models\User;

class ApprovalPembayaranNotificationService extends BaseNotificationService
{
    public const TYPE_PENDING_APPROVAL = "approval_pembayaran_pending";
    public const TYPE_APPROVED = "approval_pembayaran_approved";
    public const TYPE_REJECTED = "approval_pembayaran_rejected";

    /**
     * Notify Accounting bahwa ada pembayaran menunggu approval.
     */
    public static function notifyPendingApproval(ApprovalPembayaran $approval): int
    {
        $pengiriman = $approval->pengiriman;
        $amount = number_format($pengiriman->total_harga_kirim, 0, ',', '.');

        return static::sendToRole("manager_accounting", self::TYPE_PENDING_APPROVAL, [
            "title" => "Pembayaran Menunggu Approval",
            "message" => "Pembayaran Rp {$amount} untuk {$pengiriman->no_pengiriman} menunggu approval",
            "icon" => "money-bill-wave",
            "icon_bg" => "bg-yellow-100",
            "icon_color" => "text-yellow-600",
            "url" => "/accounting/approval-pembayaran",
            "approval_id" => $approval->id,
            "amount" => $pengiriman->total_harga_kirim,
        ]);
    }
}
```

---

## Scheduled Notifications (Laravel Scheduler)

Beberapa notifikasi dikirim secara otomatis melalui Laravel Scheduler:

### 1. Order Priority Escalation
- **Command**: `orders:escalate-priorities --notify`
- **Schedule**: Daily at 06:00 WIB
- **Target**: Order creators
- **Purpose**: Escalate order priorities and notify about urgent orders

### 2. Forecast Pending Reminder
- **Command**: `forecast:notify-pending`
- **Schedule**: Daily at 06:00 WIB
- **Target**: Manager & Staff Purchasing
- **Purpose**: Remind about pending forecasts that need processing

### 3. Pengiriman Pending Reminder
- **Command**: `pengiriman:notify-pending`
- **Schedule**: Daily at 06:00 WIB
- **Target**: Manager & Staff Purchasing
- **Purpose**: Remind about pending deliveries (status: pending, menunggu_verifikasi)

### Konfigurasi Scheduler

File: `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

// Escalate order priorities daily at 6:00 AM
Schedule::command("orders:escalate-priorities --notify")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/order-priority-escalation.log"));

// Send forecast pending reminder daily at 6:00 AM
Schedule::command("forecast:notify-pending")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/forecast-pending-reminder.log"));

// Send pengiriman pending reminder daily at 6:00 AM
Schedule::command("pengiriman:notify-pending")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/pengiriman-pending-reminder.log"));
```

### Menjalankan Scheduler

Untuk menjalankan scheduler di production, tambahkan cron job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Untuk testing lokal, jalankan:

```bash
# Test single command
php artisan forecast:notify-pending
php artisan pengiriman:notify-pending

# Run scheduler manually
php artisan schedule:run

# Watch scheduler (development)
php artisan schedule:work
```

---

## Best Practices

### 1. Naming Conventions

```php
// Type constants: {domain}_{action}
public const TYPE_ORDER_CREATED = "order_created";
public const TYPE_PENGIRIMAN_VERIFIED = "pengiriman_verified";

// Method names: notify{Action}
public static function notifyCreated(...) { }
public static function notifyApproved(...) { }
public static function notifyRejected(...) { }
```

### 2. Selalu Cek Recipient

```php
public static function notifySomething(Model $model): ?string
{
    $recipient = $model->user;
    
    // Jangan kirim jika tidak ada penerima
    if (!$recipient) {
        return null;
    }
    
    return static::send($recipient, ...);
}
```

### 3. Hindari Notifikasi ke Diri Sendiri

```php
public static function notifyApproved(Model $model, User $approvedBy): ?string
{
    $creator = $model->creator;
    
    // Jangan notify jika yang approve adalah creator sendiri
    if (!$creator || $creator->id === $approvedBy->id) {
        return null;
    }
    
    return static::send($creator, ...);
}
```

### 4. Gunakan Icon yang Konsisten

| Aksi | Icon | Background | Color |
|------|------|------------|-------|
| Menunggu/Pending | `clock` | `bg-yellow-100` | `text-yellow-600` |
| Sukses/Approved | `check-circle` | `bg-green-100` | `text-green-600` |
| Ditolak/Error | `times-circle` | `bg-red-100` | `text-red-600` |
| Info/Notifikasi | `info-circle` | `bg-blue-100` | `text-blue-600` |
| Peringatan | `exclamation-triangle` | `bg-orange-100` | `text-orange-600` |
| Pengiriman | `shipping-fast` atau `truck` | varies | varies |
| Pembayaran | `money-bill-wave` | varies | varies |
| Dokumen | `file-invoice` | varies | varies |
| Pertanyaan | `question-circle` | `bg-blue-100` | `text-blue-600` |

### 5. Sertakan Data yang Cukup

```php
return static::send($recipient, self::TYPE_SOMETHING, [
    // Required fields
    "title" => "...",
    "message" => "...",
    "icon" => "...",
    "icon_bg" => "...",
    "icon_color" => "...",
    "url" => "...",
    
    // Domain-specific data (untuk tracking/debugging)
    "order_id" => $order->id,
    "no_order" => $order->no_order,
    "amount" => $order->total_amount,
    // dll...
]);
```

### 6. Log untuk Debugging

```php
use Illuminate\Support\Facades\Log;

public static function notifySomething(Model $model): ?string
{
    $notificationId = static::send(...);
    
    Log::info("Sent notification", [
        "type" => self::TYPE_SOMETHING,
        "notification_id" => $notificationId,
        "model_id" => $model->id,
    ]);
    
    return $notificationId;
}
```

---

## Referensi API

### BaseNotificationService

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `send` | `User $user, string $type, array $data` | `?string` | Kirim notifikasi ke user |
| `sendToMany` | `$users, string $type, array $data` | `int` | Kirim ke banyak user |
| `sendToRole` | `string $role, string $type, array $data` | `int` | Kirim ke semua user dengan role |
| `getUnreadCount` | `User $user` | `int` | Hitung notifikasi unread |
| `getNotifications` | `User $user, int $limit = 10, bool $unreadOnly = false` | `Collection` | Ambil notifikasi user |
| `markAsRead` | `string $id, User $user` | `bool` | Tandai sudah dibaca |
| `markAllAsRead` | `User $user` | `int` | Tandai semua sudah dibaca |
| `delete` | `string $id, User $user` | `bool` | Hapus notifikasi |
| `find` | `string $id, User $user` | `?object` | Cari notifikasi |
| `cleanupOldNotifications` | `int $daysOld = 30` | `int` | Hapus notifikasi lama |

### Roles yang Tersedia

| Role | Description |
|------|-------------|
| `direktur` | Direktur - approval tertinggi |
| `manager_marketing` | Manager Marketing |
| `staff_marketing` | Staff Marketing |
| `manager_purchasing` | Manager Purchasing |
| `staff_purchasing` | Staff Purchasing |
| `manager_accounting` | Manager Accounting |
| `staff_accounting` | Staff Accounting |

---

## Troubleshooting

### Notifikasi Tidak Muncul

1. Cek apakah user penerima `status = 'aktif'`
2. Cek apakah notifikasi tersimpan di database: `SELECT * FROM notifications WHERE notifiable_id = {user_id}`
3. Cek browser console untuk error JavaScript
4. Pastikan API endpoint `/api/notifications` bisa diakses

### Polling Tidak Berjalan

1. Cek apakah user sudah login (authenticated)
2. Cek CSRF token di meta tag
3. Cek network tab untuk request ke `/api/notifications`

### Icon Tidak Muncul

1. Pastikan FontAwesome sudah di-load
2. Gunakan nama icon tanpa prefix `fa-` (contoh: `check-circle` bukan `fa-check-circle`)

---

## Changelog

| Tanggal | Versi | Perubahan |
|---------|-------|-----------|
| 2025-01-15 | 1.0 | Initial implementation dengan Penawaran & Order notifications |
| 2025-01-15 | 1.1 | Split ke separate service classes untuk separation of concerns |

---

## Kontributor

- Initial implementation: Development Team
- Documentation: Development Team

Untuk pertanyaan atau saran, hubungi tim development.