<div
    class="relative"
    x-data="{
        isOpen: false,
        notifications: @js($notifications),
        unreadCount: {{ $unreadCount }},
        pollInterval: null,

        init() {
            this.startPolling();
        },

        get unreadNotifications() {
            return this.notifications.filter(n => !n.read_at);
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchNotifications();
            }
        },

        startPolling() {
            this.pollInterval = setInterval(() => {
                this.fetchNotifications();
            }, 15000);
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/api/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                }
            } catch (e) {
                console.error('Failed to fetch notifications:', e);
            }
        },

        async markAsRead(id) {
            try {
                const response = await fetch('/api/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    }
                });
                if (response.ok) {
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification) {
                        notification.read_at = new Date().toISOString();
                    }
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (e) {
                console.error('Failed to mark as read:', e);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    }
                });
                if (response.ok) {
                    this.notifications.forEach(n => {
                        n.read_at = new Date().toISOString();
                    });
                    this.unreadCount = 0;
                }
            } catch (e) {
                console.error('Failed to mark all as read:', e);
            }
        },

        navigateTo(id, url) {
            this.markAsRead(id);
            this.isOpen = false;
            window.location.href = url;
        }
    }"
    x-init="init()"
    @keydown.escape.window="isOpen = false"
>
    {{-- Bell Button --}}
    <button
        @click="toggleDropdown()"
        type="button"
        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200 relative focus:outline-none"
    >
        <i class="fas fa-bell text-lg"></i>
        <span
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 min-w-[1rem] h-4 px-1 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse"
        ></span>
    </button>

    {{-- Notification Dropdown --}}
    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="isOpen = false"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden"
    >
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-4 py-3 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-bell text-indigo-600"></i>
                    <h3 class="font-semibold text-gray-900">Notifikasi</h3>
                    <span
                        x-show="unreadCount > 0"
                        x-text="unreadCount + ' baru'"
                        class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full"
                    ></span>
                </div>
                <button
                    x-show="unreadCount > 0"
                    @click="markAllAsRead()"
                    type="button"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                >
                    Tandai Semua Dibaca
                </button>
            </div>
        </div>

        {{-- Notification List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="unreadNotifications.length > 0">
                <div>
                    <template x-for="notification in unreadNotifications" :key="notification.id">
                        <div class="px-4 py-3 border-b border-gray-50 bg-blue-50/50 hover:bg-blue-100/50 transition-colors">
                            <div class="flex items-start space-x-3">
                                {{-- Icon --}}
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center"
                                        :class="notification.icon_bg"
                                    >
                                        <i
                                            class="fas"
                                            :class="'fa-' + notification.icon + ' ' + notification.icon_color"
                                        ></i>
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <p class="text-sm font-semibold text-gray-900" x-text="notification.title"></p>
                                        <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full ml-2 mt-1.5"></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-0.5 line-clamp-2" x-text="notification.message"></p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-xs text-gray-400">
                                            <i class="far fa-clock mr-1"></i>
                                            <span x-text="notification.time_ago"></span>
                                        </p>
                                        <div class="flex items-center space-x-1">
                                            {{-- Mark as Read Button --}}
                                            <button
                                                @click.stop="markAsRead(notification.id)"
                                                type="button"
                                                class="text-xs text-green-600 hover:text-green-800 font-medium transition-colors px-2 py-1 rounded hover:bg-green-100"
                                                title="Tandai sudah dibaca"
                                            >
                                                <i class="fas fa-check mr-1"></i>
                                                Dibaca
                                            </button>
                                            {{-- Go to Link --}}
                                            <button
                                                x-show="notification.url && notification.url !== '#'"
                                                @click.stop="navigateTo(notification.id, notification.url)"
                                                type="button"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors px-2 py-1 rounded hover:bg-indigo-100"
                                                title="Lihat detail"
                                            >
                                                <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="unreadNotifications.length === 0">
                <div class="px-4 py-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Tidak Ada Notifikasi Baru</p>
                    <p class="text-gray-400 text-sm mt-1">Semua notifikasi sudah dibaca</p>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <a
                    href="{{ route('notifications.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                >
                    <i class="fas fa-list mr-1"></i>
                    Lihat Semua
                </a>
                <button
                    @click="isOpen = false"
                    type="button"
                    class="text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
