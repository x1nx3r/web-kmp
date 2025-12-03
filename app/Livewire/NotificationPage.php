<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class NotificationPage extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read

    protected $paginationTheme = 'tailwind';

    protected $queryString = ['filter'];

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();

        if ($user) {
            NotificationService::markAsRead($notificationId, $user);
        }
    }

    public function markAllAsRead()
    {
        $user = auth()->user();

        if ($user) {
            NotificationService::markAllAsRead($user);
            session()->flash('message', 'Semua notifikasi telah ditandai sebagai dibaca');
        }
    }

    public function deleteNotification($notificationId)
    {
        $user = auth()->user();

        if ($user) {
            DB::table('notifications')
                ->where('id', $notificationId)
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->delete();

            session()->flash('message', 'Notifikasi berhasil dihapus');
        }
    }

    public function deleteAllRead()
    {
        $user = auth()->user();

        if ($user) {
            $count = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereNotNull('read_at')
                ->delete();

            session()->flash('message', "{$count} notifikasi yang sudah dibaca berhasil dihapus");
        }
    }

    public function navigateToNotification($notificationId, $url)
    {
        $this->markAsRead($notificationId);
        return redirect($url);
    }

    protected function getNotificationsQuery()
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        $query = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query;
    }

    protected function getStats()
    {
        $user = auth()->user();

        if (!$user) {
            return [
                'all' => 0,
                'unread' => 0,
                'read' => 0,
            ];
        }

        $baseQuery = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id);

        return [
            'all' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->whereNull('read_at')->count(),
            'read' => (clone $baseQuery)->whereNotNull('read_at')->count(),
        ];
    }

    /**
     * Convert timestamp to Indonesian "time ago" format.
     */
    protected function timeAgo($timestamp): string
    {
        $time = \Carbon\Carbon::parse($timestamp);
        $diff = $time->diffInSeconds(now());

        if ($diff < 60) {
            return 'Baru saja';
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

        return $time->format('d M Y, H:i');
    }

    public function render()
    {
        $notifications = $this->getNotificationsQuery()->paginate(15);

        // Transform the notifications
        $notifications->getCollection()->transform(function ($notification) {
            $data = json_decode($notification->data, true);
            $notification->data = $data;
            $notification->title = $data['title'] ?? 'Notifikasi';
            $notification->message = $data['message'] ?? '';
            $notification->icon = $data['icon'] ?? 'bell';
            $notification->icon_bg = $data['icon_bg'] ?? 'bg-blue-100';
            $notification->icon_color = $data['icon_color'] ?? 'text-blue-600';
            $notification->url = $data['url'] ?? '#';
            $notification->time_ago = $this->timeAgo($notification->created_at);
            $notification->formatted_date = \Carbon\Carbon::parse($notification->created_at)->format('d M Y, H:i');
            return $notification;
        });

        return view('livewire.notification-page', [
            'notifications' => $notifications,
            'stats' => $this->getStats(),
        ]);
    }
}
