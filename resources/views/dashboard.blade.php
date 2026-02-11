<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Restaurant Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-gray-600">Welcome back, <span class="font-bold">{{ Auth::user()->name }}</span>! Here is your restaurant overview.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Orders Card -->
                <a href="{{ route('orders.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <x-heroicon-o-shopping-bag class="w-8 h-8 text-indigo-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Orders') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Manage active orders') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Tables Card -->
                <a href="{{ route('tables.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-green-500">
                    <div class="flex items-center">
                        <x-heroicon-o-squares-2x2 class="w-8 h-8 text-green-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Tables') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Layout and availability') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Reservations Card -->
                <a href="{{ route('reservations.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <x-heroicon-o-calendar-days class="w-8 h-8 text-yellow-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Reservations') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Bookings and schedule') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Menu Card -->
                <a href="{{ route('menu-items.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-red-500">
                    <div class="flex items-center">
                        <x-heroicon-o-book-open class="w-8 h-8 text-red-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Menu') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Pricing and availability') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Dishes Card -->
                <a href="{{ route('dishes.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-orange-500">
                    <div class="flex items-center">
                        <x-heroicon-o-cake class="w-8 h-8 text-orange-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Dishes') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Base dish definitions') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Staff Card -->
                <a href="{{ route('users.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <x-heroicon-o-users class="w-8 h-8 text-blue-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Staff') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Users and permissions') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Shifts Card -->
                <a href="{{ route('shifts.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <x-heroicon-o-clock class="w-8 h-8 text-purple-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Shifts') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Work schedule') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Invoices Card -->
                <a href="{{ route('invoices.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group border-l-4 border-gray-500">
                    <div class="flex items-center">
                        <x-heroicon-o-credit-card class="w-8 h-8 text-gray-500 group-hover:scale-110 transition" />
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Invoices') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Financial records') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
