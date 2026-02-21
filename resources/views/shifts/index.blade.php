<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Shifts') }}
            </h2>
            <a href="{{ route('shifts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ __('Schedule Shift') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'table' }">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm sm:rounded-r-lg" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Tab navigation --}}
            <div class="mb-4 border-b border-gray-200">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button @click="tab = 'table'"
                        :class="tab === 'table' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        <x-heroicon-o-table-cells class="w-4 h-4 mr-2" />
                        {{ __('Table View') }}
                    </button>
                    <button @click="tab = 'calendar'"
                        :class="tab === 'calendar' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        <x-heroicon-o-calendar-days class="w-4 h-4 mr-2" />
                        {{ __('Calendar View') }}
                    </button>
                </nav>
            </div>

            {{-- Table view --}}
            <div x-show="tab === 'table'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($shifts as $shift)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $shift->date->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $shift->user->first_name ?? 'Unknown' }} {{ $shift->user->last_name ?? '' }}
                                            <div class="text-xs text-gray-500">{{ ucfirst($shift->user->role->value ?? 'N/A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $shift->shift_type === \App\Enums\ShiftType::Morning ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $shift->shift_type === \App\Enums\ShiftType::Evening ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ $shift->shift_type === \App\Enums\ShiftType::FullDay ? 'bg-green-100 text-green-800' : '' }}">
                                                {{ $shift->shift_type->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                            <a href="{{ route('shifts.edit', $shift) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <form action="{{ route('shifts.destroy', $shift) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this shift?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-1 rounded transition">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            {{ __('No shifts scheduled.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $shifts->links() }}
                    </div>
                </div>
            </div>

            {{-- Calendar view --}}
            <div x-show="tab === 'calendar'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    {{-- Role filter --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-sm font-medium text-gray-500 mr-1">{{ __('Filter:') }}</span>
                        <button data-role-filter="waiter" class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            {{ __('Waiters') }}
                        </button>
                        <button data-role-filter="chef" class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            {{ __('Chefs') }}
                        </button>
                        <button data-role-filter="bartender" class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            {{ __('Bartenders') }}
                        </button>
                        <button data-role-filter="manager" class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            {{ __('Managers') }}
                        </button>
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-4 mb-4 text-sm">
                        <span class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-amber-500 mr-1.5"></span>
                            {{ __('Morning') }}
                        </span>
                        <span class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-indigo-500 mr-1.5"></span>
                            {{ __('Evening') }}
                        </span>
                        <span class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span>
                            {{ __('Full Day') }}
                        </span>
                        <span class="text-gray-400 ml-auto text-xs">{{ __('Click an event to edit · Click a date to create') }}</span>
                    </div>

                    <div id="shifts-calendar"
                         data-events-url="{{ route('shifts.calendar-events') }}"
                         data-create-url="{{ route('shifts.create') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/shifts-calendar.js')
</x-app-layout>
