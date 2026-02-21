<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Reservations')" :actionUrl="route('reservations.create')" :actionLabel="__('New Reservation')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'calendar' }">
            <x-flash-message type="success" />
            <x-flash-message type="error" />

            <x-tabs
                :tabs="['calendar' => __('Calendar View'), 'table' => __('Table View')]"
                default="calendar"
                :icons="['calendar' => 'heroicon-o-calendar-days', 'table' => 'heroicon-o-table-cells']"
            />

            {{-- Calendar view --}}
            <div x-show="tab === 'calendar'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-wrap gap-4 mb-4 text-sm">
                        <span class="text-gray-400 text-xs">{{ __('Click an event to edit · Click a date to create') }}</span>
                    </div>
                    <div id="reservations-calendar"
                         data-events-url="{{ route('reservations.calendar-events') }}"
                         data-create-url="{{ route('reservations.create') }}">
                    </div>
                </div>
            </div>

            {{-- Table view --}}
            <div x-show="tab === 'table'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($reservations as $reservation)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-bold">{{ $reservation->reservation_date }}</div>
                                            <div class="text-gray-500">{{ $reservation->reservation_time }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-bold">{{ $reservation->customer_name }}</div>
                                            <div class="text-gray-500">{{ $reservation->phone_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            #{{ $reservation->table->table_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $reservation->party_size }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                            <a href="{{ route('reservations.edit', $reservation) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <x-delete-button :route="route('reservations.destroy', $reservation)" :confirmMessage="__('Are you sure you want to delete this reservation?')" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            {{ __('No reservations found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $reservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/reservations-calendar.js')
</x-app-layout>
