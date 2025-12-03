<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Livewire\Component;

class NotificationBell extends Component
{
    public function render()
    {
        $user = auth()->user();
        $unreadCount = 0;
        $notifications = [];

        if ($user) {
            $unreadCount = NotificationService::getUnreadCount($user);
            $notifications = NotificationService::getNotifications($user, 10)
                ->map(function ($notification) {
                    return [
                        "id" => $notification->id,
                        "type" => $notification->type,
                        "title" => $notification->data["title"] ?? "Notifikasi",
                        "message" => $notification->data["message"] ?? "",
                        "icon" => $notification->data["icon"] ?? "bell",
                        "icon_bg" =>
                            $notification->data["icon_bg"] ?? "bg-blue-100",
                        "icon_color" =>
                            $notification->data["icon_color"] ??
                            "text-blue-600",
                        "url" => $notification->data["url"] ?? "#",
                        "read_at" => $notification->read_at,
                        "created_at" => $notification->created_at,
                        "time_ago" => $this->timeAgo($notification->created_at),
                    ];
                })
                ->toArray();
        }

        return view("livewire.notification-bell", [
            "unreadCount" => $unreadCount,
            "notifications" => $notifications,
        ]);
    }

    /**
     * Convert timestamp to Indonesian "time ago" format.
     */
    protected function timeAgo($timestamp): string
    {
        $time = \Carbon\Carbon::parse($timestamp);
        $diff = $time->diffInSeconds(now());

        if ($diff < 60) {
            return "Baru saja";
        }

        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} menit yang lalu";
        }

        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} jam yang lalu";
        }

        if ($diff < 604800) {
            $days = floor($diff / 86400);
            return "{$days} hari yang lalu";
        }

        if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "{$weeks} minggu yang lalu";
        }

        return $time->format("d M Y");
    }
}
