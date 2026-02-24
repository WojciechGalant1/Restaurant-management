<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('New Reservation')">
            <x-slot name="action">
                <a href="{{ route('reservations.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6" x-data="reservationCreateForm()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message type="success" />
            <x-flash-message type="error" />
            <form method="POST" action="{{ route('reservations.store') }}"
                  x-on:submit="if (!selectedTableId) { $event.preventDefault(); alert('{{ __('Please select a table.') }}'); return false; }">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Left column: Reservation data --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('Reservation Details') }}</h3>

                        {{-- Party Size, Date, Time, Duration (first) --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="party_size" :value="__('Party Size')" />
                                <x-text-input id="party_size" name="party_size" type="number" min="1" class="mt-1 block w-full"
                                              x-model.number="partySize" @input="fetchAvailableTables()" required />
                                <x-input-error :messages="$errors->get('party_size')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="reservation_date" :value="__('Date')" />
                                <x-text-input id="reservation_date" name="reservation_date" type="date" class="mt-1 block w-full"
                                              x-model="reservationDate" @input="fetchAvailableTables()" required />
                                <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="reservation_time" :value="__('Time')" />
                                <x-text-input id="reservation_time" name="reservation_time" type="time" class="mt-1 block w-full"
                                              x-model="reservationTime" @input="fetchAvailableTables()" required />
                                <x-input-error :messages="$errors->get('reservation_time')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="duration_minutes" :value="__('Duration')" />
                                <select id="duration_minutes" name="duration_minutes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        x-model.number="durationMinutes" @change="fetchAvailableTables()">
                                    @foreach([45, 60, 90, 120, 180] as $mins)
                                        <option value="{{ $mins }}">{{ $mins }} {{ __('min') }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Customer Name, Phone --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="customer_name" :value="__('Customer Name')" />
                                <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full"
                                              x-model="customerName" required autofocus />
                                <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="phone_number" :value="__('Phone Number')" />
                                <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full"
                                              x-model="phoneNumber" @blur="lookupCustomer()" required />
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Quick tags --}}
                        <div>
                            <x-input-label class="mb-2 block" :value="__('Quick tags')" />
                            <div class="flex flex-wrap gap-2">
                                <template x-for="tag in tagOptions" :key="tag.id">
                                    <button type="button"
                                            @click="toggleTag(tag.id)"
                                            :class="selectedTags.includes(tag.id)
                                                ? 'bg-indigo-100 text-indigo-800 border-indigo-300'
                                                : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200'"
                                            class="px-3 py-1.5 rounded-lg text-sm font-medium border transition">
                                        <span x-text="tag.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Notes (tags + free text) --}}
                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      x-model="notes" placeholder="{{ __('Additional notes...') }}"></textarea>
                            <input type="hidden" name="notes" :value="notesWithTags" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Right column: Available tables --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Available Tables') }}</h3>

                        <div class="mb-4 flex flex-wrap gap-2 items-center">
                            <input type="text" x-model="roomFilter" placeholder="{{ __('Filter by room...') }}"
                                   class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-40" />
                            <button type="button" @click="autoAssign()"
                                    :disabled="availableTables.length === 0"
                                    class="px-3 py-1.5 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                {{ __('Auto assign best table') }}
                            </button>
                        </div>

                        <input type="hidden" name="table_id" :value="selectedTableId" />

                        <div x-show="loading" class="py-8 text-center text-gray-500">
                            {{ __('Loading available tables...') }}
                        </div>

                        <div x-show="!loading && !canFetch" class="py-8 text-center text-gray-500">
                            {{ __('Enter date, time and party size to see available tables.') }}
                        </div>

                        <div x-show="!loading && canFetch && availableTables.length === 0" class="py-8 text-center text-amber-600">
                            {{ __('No tables available for this slot. Try a different time or party size.') }}
                        </div>

                        <div x-show="!loading && canFetch && availableTables.length > 0" class="space-y-4">
                            <template x-for="room in filteredRooms" :key="room.room_id || 'unassigned'">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2"
                                        :style="room.room_color ? `color: ${room.room_color}` : ''">
                                        <span class="w-2 h-2 rounded-full" :style="room.room_color ? `background: ${room.room_color}` : 'background: #6b7280'"></span>
                                        <span x-text="room.room_name"></span>
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <template x-for="table in room.tables" :key="table.id">
                                            <button type="button"
                                                    @click="selectTable(table.id)"
                                                    :class="selectedTableId === table.id
                                                        ? 'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-400'
                                                        : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'"
                                                    class="p-3 rounded-lg border-2 text-left transition">
                                                <div class="font-bold text-gray-900">#<span x-text="table.table_number"></span></div>
                                                <div class="text-xs text-gray-500" x-text="table.capacity + ' {{ __('seats') }}'"></div>
                                                <div x-show="table.has_conflict_risk" class="mt-1 text-xs text-amber-600 flex items-center gap-0.5">
                                                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                                                    {{ __('Conflict risk') }}
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <x-secondary-button x-on:click="window.history.back()" type="button" class="mr-3">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-primary-button x-bind:disabled="!selectedTableId">
                        {{ __('Save Reservation') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.__RESERVATION_CREATE__ = @js([
            'availableTablesUrl' => route('reservations.available-tables'),
            'customerByPhoneUrl' => route('reservations.customer-by-phone'),
            'partySize' => old('party_size', 2),
            'reservationDate' => old('reservation_date', now()->format('Y-m-d')),
            'reservationTime' => old('reservation_time', '19:00'),
            'durationMinutes' => (int) old('duration_minutes', 120),
            'customerName' => old('customer_name', ''),
            'phoneNumber' => old('phone_number', ''),
            'notes' => old('notes', ''),
        ]);
    </script>
</x-app-layout>
