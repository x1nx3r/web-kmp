<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
                        <p class="text-gray-600 text-sm">Semua notifikasi Anda</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($stats['unread'] > 0)
                        <button
                            wire:click="markAllAsRead"
                            class="px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium rounded-lg transition-colors text-sm"
                        >
                            <i class="fas fa-check-double mr-2"></i>
                            Tandai Semua Dibaca
                        </button>
                    @endif
                    @if($stats['read'] > 0)
                        <button
                            wire:click="deleteAllRead"
                            wire:confirm="Apakah Anda yakin ingin menghapus semua notifikasi yang sudah dibaca?"
                            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg transition-colors text-sm"
                        >
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Sudah Dibaca
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('message') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div
                wire:click="$set('filter', 'all')"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-pointer transition-all hover:shadow-md {{ $filter === 'all' ? 'ring-2 ring-indigo-500' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Semua</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['all'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                wire:click="$set('filter', 'unread')"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-pointer transition-all hover:shadow-md {{ $filter === 'unread' ? 'ring-2 ring-indigo-500' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Belum Dibaca</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['unread'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-envelope text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                wire:click="$set('filter', 'read')"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-pointer transition-all hover:shadow-md {{ $filter === 'read' ? 'ring-2 ring-indigo-500' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Sudah Dibaca</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['read'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-envelope-open text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            {{-- List Header --}}
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">
                        @if($filter === 'all')
                            Semua Notifikasi
                        @elseif($filter === 'unread')
                            Notifikasi Belum Dibaca
                        @else
                            Notifikasi Sudah Dibaca
                        @endif
                    </h3>
                    <span class="text-sm text-gray-500">
                        {{ $notifications->total() }} notifikasi
                    </span>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors {{ !$notification->read_at ? 'bg-blue-50/50' : '' }}">
                        <div class="flex items-start space-x-4">
                            {{-- Icon --}}
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 {{ $notification->icon_bg }} rounded-full flex items-center justify-center">
                                    <i class="fas fa-{{ $notification->icon }} {{ $notification->icon_color }} text-lg"></i>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <p class="text-base font-medium text-gray-900 {{ !$notification->read_at ? 'font-semibold' : '' }}">
                                                {{ $notification->title }}
                                            </p>
                                            @if(!$notification->read_at)
                                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-xs text-gray-400">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ $notification->time_ago }}
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                <i class="far fa-calendar mr-1"></i>
                                                {{ $notification->formatted_date }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center space-x-2 ml-4">
                                        @if($notification->url && $notification->url !== '#')
                                            <button
                                                wire:click="navigateToNotification('{{ $notification->id }}', '{{ $notification->url }}')"
                                                class="px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition-colors text-sm"
                                                title="Lihat Detail"
                                            >
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        @endif

                                        @if(!$notification->read_at)
                                            <button
                                                wire:click="markAsRead('{{ $notification->id }}')"
                                                class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors text-sm"
                                                title="Tandai Dibaca"
                                            >
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        <button
                                            wire:click="deleteNotification('{{ $notification->id }}')"
                                            wire:confirm="Apakah Anda yakin ingin menghapus notifikasi ini?"
                                            class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors text-sm"
                                            title="Hapus"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-bell-slash text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Notifikasi</h3>
                        <p class="text-gray-500">
                            @if($filter === 'unread')
                                Semua notifikasi sudah dibaca.
                            @elseif($filter === 'read')
                                Belum ada notifikasi yang sudah dibaca.
                            @else
                                Anda belum memiliki notifikasi.
                            @endif
                        </p>
                        @if($filter !== 'all')
                            <button
                                wire:click="$set('filter', 'all')"
                                class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors text-sm"
                            >
                                Lihat Semua Notifikasi
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
