<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="($seesAllShifts ?? $isManager ?? false) ? __('Staff Shifts') : __('My Shifts')">
            @can('create', App\Models\Shift::class)
                <x-slot name="action">
                    <a href="{{ route('shifts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
                        <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                        {{ __('Schedule Shift') }}
                    </a>
                </x-slot>
            @endcan
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'calendar' }">
            <x-flash-message type="success" />
            <x-flash-message type="error" />

            <x-tabs
                :tabs="['calendar' => __('Calendar View'), 'table' => __('Table View')]"
                default="calendar"
                :icons="['calendar' => 'heroicon-o-calendar-days', 'table' => 'heroicon-o-table-cells']"
            />

            {{-- Table view --}}
            <div x-show="tab === 'table'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    @if ($isManager ?? false)
                    {{-- Role filter (manager only) --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-sm font-medium text-gray-500 mr-1">{{ __('Filter:') }}</span>
                        <a href="{{ route('shifts.index', array_filter(['role' => null])) }}"
                           class="px-3 py-1 text-xs font-medium rounded-full transition {{ empty($roleFilter) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('All') }}
                        </a>
                        <a href="{{ route('shifts.index', ['role' => 'waiter']) }}"
                           class="px-3 py-1 text-xs font-medium rounded-full transition {{ ($roleFilter ?? '') === 'waiter' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Waiters') }}
                        </a>
                        <a href="{{ route('shifts.index', ['role' => 'chef']) }}"
                           class="px-3 py-1 text-xs font-medium rounded-full transition {{ ($roleFilter ?? '') === 'chef' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Chefs') }}
                        </a>
                        <a href="{{ route('shifts.index', ['role' => 'bartender']) }}"
                           class="px-3 py-1 text-xs font-medium rounded-full transition {{ ($roleFilter ?? '') === 'bartender' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Bartenders') }}
                        </a>
                        <a href="{{ route('shifts.index', ['role' => 'manager']) }}"
                           class="px-3 py-1 text-xs font-medium rounded-full transition {{ ($roleFilter ?? '') === 'manager' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Managers') }}
                        </a>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    @if ($seesAllShifts ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                    @if ($isManager ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($shifts as $shift)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $shift->date->format('Y-m-d') }}
                                        </td>
                                        @if ($seesAllShifts ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $shift->user->first_name ?? 'Unknown' }} {{ $shift->user->last_name ?? '' }}
                                            <div class="text-xs text-gray-500">{{ ucfirst($shift->user->role->value ?? 'N/A') }}</div>
                                        </td>
                                        @endif
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
                                        @if ($isManager ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                            <a href="{{ route('shifts.edit', $shift) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <x-delete-button :route="route('shifts.destroy', $shift)" :confirmMessage="__('Are you sure you want to delete this shift?')" />
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ($isManager ?? false) ? 5 : 3 }}" class="px-6 py-10 text-center text-gray-500 italic">
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
                    @if ($isManager ?? false)
                    {{-- Role filter (manager only) --}}
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
                    @endif

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
                        @if ($isManager ?? false)
                        <span class="text-gray-400 ml-auto text-xs">{{ __('Click an event to edit · Click a date to create') }}</span>
                        @else
                        <span class="text-gray-400 ml-auto text-xs">{{ __('Your scheduled shifts') }}</span>
                        @endif
                    </div>

                    <div id="shifts-calendar"
                         data-events-url="{{ route('shifts.calendar-events') }}"
                         data-create-url="{{ ($isManager ?? false) ? route('shifts.create') : '' }}"
                         data-can-manage="{{ ($isManager ?? false) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/shifts-calendar.js')
</x-app-layout>
