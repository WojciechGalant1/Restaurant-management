<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <style>
            [x-cloak]{display:none!important}
            .sidebar-panel { transition: transform 300ms ease-in-out, width 300ms ease-in-out; }
            @media (min-width: 1024px) {
                #main-wrapper { margin-left: 16rem; transition: margin-left 300ms ease-in-out; }
                #main-wrapper.sidebar-collapsed { margin-left: 4.5rem; }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100"
             x-data="{ sidebarOpen: false, collapsed: localStorage.getItem('sidebar-collapsed') === 'true' }">

            @include('layouts.sidebar')

            {{-- Main wrapper --}}
            <div id="main-wrapper" :class="{ 'sidebar-collapsed': collapsed }">

                {{-- Top bar (hamburger + user actions) --}}
                <header class="sticky top-0 z-30 flex items-center justify-between h-14 bg-white border-b border-gray-200 px-4 sm:px-6">
                    {{-- Left: mobile hamburger --}}
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </button>

                    <div class="hidden lg:block"></div>

                    {{-- Right: notifications dropdown + profile dropdown --}}
                    <div class="flex items-center gap-x-3">
                        {{-- Notifications dropdown --}}
                        <div class="relative"
                             x-data="{
                                open: false,
                                notifications: @js($notifications ?? []),
                                userRole: @js($notificationUserRole),
                                dismissedHash: (function(){ try { return sessionStorage.getItem('notification_dismissed_hash') || ''; } catch(e) { return ''; } })(),
                                get notificationHash() {
                                    if (this.notifications.length === 0) return '';
                                    try {
                                        return btoa(JSON.stringify(this.notifications.map(n => [n.type, n.message, n.link])));
                                    } catch (e) { return ''; }
                                },
                                get hasUnread() {
                                    return this.notifications.length > 0 && this.dismissedHash !== this.notificationHash;
                                },
                                get unreadCount() {
                                    return this.hasUnread ? this.notifications.length : 0;
                                },
                                markAllRead() {
                                    this.dismissedHash = this.notificationHash;
                                    try { sessionStorage.setItem('notification_dismissed_hash', this.notificationHash); } catch(e) {}
                                },
                                playNotificationSound() {
                                    if (!['chef', 'waiter', 'bartender'].includes(this.userRole)) return;
                                    try {
                                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                        const osc = ctx.createOscillator();
                                        const gain = ctx.createGain();
                                        osc.connect(gain);
                                        gain.connect(ctx.destination);
                                        osc.frequency.value = 880;
                                        osc.type = 'sine';
                                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                                        osc.start(ctx.currentTime);
                                        osc.stop(ctx.currentTime + 0.4);
                                    } catch (e) {}
                                }
                             }"
                             x-init="if (hasUnread) { $nextTick(() => playNotificationSound()) }"
                             @click.outside="open = false">
                            <button @click="open = ! open" class="relative p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="{{ __('Notifications') }}">
                                <x-heroicon-o-bell class="w-5 h-5" />
                                <span x-show="unreadCount > 0"
                                      x-cloak
                                      class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
                                      x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </button>
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 z-50 mt-2 w-80 max-h-96 overflow-y-auto rounded-md shadow-lg ring-1 ring-black ring-opacity-5 bg-white"
                                 style="display: none;">
                                <div class="py-1">
                                    <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                        <span class="text-sm font-semibold text-gray-700">{{ __('Notifications') }}</span>
                                        <button x-show="hasUnread"
                                                x-cloak
                                                type="button"
                                                @click="markAllRead()"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ __('Mark all as read') }}
                                        </button>
                                    </div>
                                    <template x-for="(alert, i) in notifications" :key="i">
                                        <a :href="alert.link" class="block px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                                            <div class="flex items-start gap-2">
                                                <span class="shrink-0 mt-0.5 w-2 h-2 rounded-full"
                                                      :class="{
                                                          'bg-red-500': alert.severity === 'critical',
                                                          'bg-amber-500': alert.severity === 'warning',
                                                          'bg-blue-500': alert.severity === 'info'
                                                      }"></span>
                                                <span class="text-sm text-gray-800" x-text="alert.message"></span>
                                            </div>
                                        </a>
                                    </template>
                                    <div x-show="notifications.length === 0" class="px-4 py-6 text-sm text-gray-500 text-center">
                                        {{ __('No new notifications') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Profile dropdown --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-x-2 px-2 py-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm shrink-0">
                                        {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}
                                    </div>
                                    <span class="hidden sm:inline font-medium">{{ Auth::user()->name }}</span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 hidden sm:block" />
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Page heading --}}
                @if (isset($header))
                    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
                        {{ $header }}
                    </div>
                @endif

                {{-- Page Content --}}
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
