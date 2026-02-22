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

                    {{-- Right: notifications placeholder + profile dropdown --}}
                    <div class="flex items-center gap-x-3">
                        {{-- Notifications placeholder --}}
                        <button class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="{{ __('Notifications') }}">
                            <x-heroicon-o-bell class="w-5 h-5" />
                        </button>

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
