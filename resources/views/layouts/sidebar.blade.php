@php use App\Enums\UserRole; @endphp

{{-- Mobile backdrop --}}
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

{{-- Sidebar --}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :style="'width:' + (collapsed ? '4.5rem' : '16rem')"
    class="sidebar-panel fixed inset-y-0 left-0 z-50 flex flex-col h-screen bg-white border-r border-gray-200 overflow-hidden"
    @click.outside.window="if(window.innerWidth < 1024) sidebarOpen = false"
>
    {{-- Logo area --}}
    <div class="flex items-center h-16 px-4 border-b border-gray-200 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-x-3 min-w-0">
            <x-application-logo class="block h-8 w-8 shrink-0 fill-current text-indigo-600" />
            <span x-show="!collapsed" x-transition.opacity.duration.200ms class="text-lg font-bold text-gray-800 truncate">
                {{ config('app.name', 'Restaurant') }}
            </span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        {{-- Main --}}
        <div class="space-y-1">
            <p x-show="!collapsed" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Main') }}</p>
            @if(in_array(auth()->user()->role, [UserRole::Manager]))
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="heroicon-o-home">
                {{ __('Dashboard') }}
            @endif
            </x-sidebar-link>
            <x-sidebar-link :href="route('shifts.index')" :active="request()->is('shifts*')" icon="heroicon-o-clock">
                @if(in_array(auth()->user()->role, [UserRole::Manager]))
                {{ __('All Shifts') }}
                @else
                {{ __('My Shifts') }}
                @endif
            </x-sidebar-link>
        </div>

        {{-- Service --}}
        @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Waiter, UserRole::Host]))
        <div class="space-y-1">
            <p x-show="!collapsed" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Service') }}</p>
            @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Host]))
            <x-sidebar-link :href="route('reservations.index')" :active="request()->is('reservations*')" icon="heroicon-o-calendar-days">
                {{ __('Reservations') }}
            </x-sidebar-link>
            @endif
            @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Host]))
            <x-sidebar-link :href="route('host.today')" :active="request()->routeIs('host.today')" icon="heroicon-o-calendar">
                {{ __('Today') }}
            </x-sidebar-link>
            @endif
            @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Waiter]))
            <x-sidebar-link :href="route('waiter.index')" :active="request()->is('waiter*')" icon="heroicon-o-bell-alert">
                {{ __('Waiter') }}
            </x-sidebar-link>
            @endif
            @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Waiter, UserRole::Host]))
            <x-sidebar-link :href="route('tables.index')" :active="request()->is('tables*')" icon="heroicon-o-squares-2x2">
                {{ __('Tables') }}
            </x-sidebar-link>
            @endif
        </div>
        @endif

        {{-- Kitchen --}}
        @if(in_array(auth()->user()->role, [UserRole::Manager, UserRole::Chef, UserRole::Bartender]))
        <div class="space-y-1">
            <p x-show="!collapsed" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Kitchen') }}</p>
            <x-sidebar-link :href="route('kitchen.index')" :active="request()->is('kitchen*')" icon="heroicon-o-fire">
                {{ __('Kitchen') }}
            </x-sidebar-link>
            <x-sidebar-link :href="route('menu-items.index')" :active="request()->is('menu-items*')" icon="heroicon-o-book-open">
                {{ __('Menu') }}
            </x-sidebar-link>
            <x-sidebar-link :href="route('dishes.index')" :active="request()->is('dishes*')" icon="heroicon-o-cake">
                {{ __('Dishes') }}
            </x-sidebar-link>
        </div>
        @endif

        {{-- Management --}}
        @if(auth()->user()->role === UserRole::Manager)
        <div class="space-y-1">
            <p x-show="!collapsed" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Management') }}</p>
            <x-sidebar-link :href="route('rooms.index')" :active="request()->is('rooms*')" icon="heroicon-o-building-office">
                {{ __('Rooms') }}
            </x-sidebar-link>
            
            <x-sidebar-link :href="route('orders.index')" :active="request()->is('orders*')" icon="heroicon-o-clipboard-document-list">
                {{ __('Orders') }}
            </x-sidebar-link>
            <x-sidebar-link :href="route('users.index')" :active="request()->is('users*')" icon="heroicon-o-users">
                {{ __('Staff') }}
            </x-sidebar-link>
            <x-sidebar-link :href="route('invoices.index')" :active="request()->is('invoices*')" icon="heroicon-o-document-text">
                {{ __('Invoices') }}
            </x-sidebar-link>
        </div>
        @endif
    </nav>

    {{-- Collapse toggle (desktop only) --}}
    <div class="hidden lg:flex items-center justify-end px-3 py-2 border-t border-gray-200">
        <button @click="collapsed = !collapsed; localStorage.setItem('sidebar-collapsed', collapsed)"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
            <x-heroicon-o-chevron-left class="w-5 h-5 transition-transform duration-300" x-bind:class="collapsed && 'rotate-180'" />
        </button>
    </div>

    {{-- User info (name only, actions in top bar) --}}
    <div class="border-t border-gray-200 px-3 py-3">
        <div class="flex items-center gap-x-3 min-w-0">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm shrink-0">
                {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}
            </div>
            <div x-show="!collapsed" x-transition.opacity.duration.200ms class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
