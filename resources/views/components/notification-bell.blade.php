{{-- Notification Bell Dropdown Component --}}
<div x-data="notificationBell()" x-init="init()" class="relative">
    {{-- Bell Button --}}
    <button @click="toggle()" 
            class="relative p-2.5 text-smoke-500 hover:text-ink-900 rounded-xl hover:bg-smoke-100 transition-all duration-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        {{-- Notification Badge --}}
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              x-cloak
              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-xs font-medium text-white bg-accent-red rounded-full flex items-center justify-center">
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @click.away="open = false"
         x-cloak
         class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-lg border border-smoke-200 z-50 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-smoke-200 flex items-center justify-between">
            <h3 class="font-medium text-ink-900">Notifications</h3>
            <div class="flex items-center gap-2">
                <button x-show="unreadCount > 0" 
                        @click="markAllRead()"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Mark all read
                </button>
                <a href="{{ route('notifications.index') }}" class="text-xs text-smoke-500 hover:text-ink-900">
                    View all
                </a>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading">
                <div class="p-8 text-center">
                    <svg class="w-6 h-6 mx-auto text-smoke-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="p-8 text-center text-smoke-500">
                    <svg class="w-10 h-10 mx-auto text-smoke-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm">No notifications</p>
                </div>
            </template>

            <template x-if="!loading && notifications.length > 0">
                <div class="divide-y divide-smoke-100">
                    <template x-for="notification in notifications" :key="notification.id">
                        <a :href="notification.data.action_url || '#'" 
                           @click="markRead(notification.id)"
                           class="block px-4 py-3 hover:bg-smoke-50 transition-colors"
                           :class="{ 'bg-blue-50': !notification.read_at }">
                            <div class="flex gap-3">
                                {{-- Icon --}}
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center"
                                     :class="getIconBgClass(notification.data.icon_color)">
                                    <svg class="w-5 h-5" :class="getIconTextClass(notification.data.icon_color)" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              :d="getIconPath(notification.data.icon)"/>
                                    </svg>
                                </div>
                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-ink-900 truncate" x-text="notification.data.title"></p>
                                    <p class="text-xs text-smoke-600 line-clamp-2" x-text="notification.data.message"></p>
                                    <p class="text-xs text-smoke-400 mt-1" x-text="notification.created_at"></p>
                                </div>
                                {{-- Unread indicator --}}
                                <div x-show="!notification.read_at" class="flex-shrink-0">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full block"></span>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-2 border-t border-smoke-200 bg-smoke-50">
            <a href="{{ route('notifications.settings') }}" class="text-xs text-smoke-500 hover:text-ink-900 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Notification settings
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: {{ Auth::user()->unreadNotifications()->count() }},

        init() {
            // Refresh count periodically
            setInterval(() => this.fetchNotifications(true), 60000); // Every minute
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications(silent = false) {
            if (!silent) this.loading = true;
            try {
                const response = await fetch('{{ route("notifications.fetch") }}');
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (e) {
                console.error('Failed to fetch notifications', e);
            }
            this.loading = false;
        },

        async markRead(id) {
            try {
                await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                this.notifications = this.notifications.map(n => 
                    n.id === id ? { ...n, read_at: new Date().toISOString() } : n
                );
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            } catch (e) {
                console.error('Failed to mark notification as read', e);
            }
        },

        async markAllRead() {
            try {
                await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                this.notifications = this.notifications.map(n => ({ ...n, read_at: new Date().toISOString() }));
                this.unreadCount = 0;
            } catch (e) {
                console.error('Failed to mark all notifications as read', e);
            }
        },

        getIconBgClass(color) {
            const classes = {
                'green': 'bg-green-100',
                'red': 'bg-red-100',
                'blue': 'bg-blue-100',
                'amber': 'bg-amber-100',
                'smoke': 'bg-smoke-100',
            };
            return classes[color] || 'bg-smoke-100';
        },

        getIconTextClass(color) {
            const classes = {
                'green': 'text-green-600',
                'red': 'text-red-600',
                'blue': 'text-blue-600',
                'amber': 'text-amber-600',
                'smoke': 'text-smoke-600',
            };
            return classes[color] || 'text-smoke-600';
        },

        getIconPath(icon) {
            const paths = {
                'requisition': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'purchase-order': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'goods-receipt': 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'check': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'x': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'budget': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'stock': 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
                'asset': 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                'bell': 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            };
            return paths[icon] || paths['bell'];
        }
    }
}
</script>
