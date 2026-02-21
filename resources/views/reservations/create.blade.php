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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Name -->
                        <div>
                            <x-input-label for="customer_name" :value="__('Customer Name')" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>

                        <!-- Table -->
                        <div>
                            <x-input-label for="table_id" :value="__('Table')" />
                            <select id="table_id" name="table_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}">Table #{{ $table->table_number }} ({{ $table->capacity }} seats)</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('table_id')" class="mt-2" />
                        </div>

                        <!-- Party Size -->
                        <div>
                            <x-input-label for="party_size" :value="__('Party Size')" />
                            <x-text-input id="party_size" name="party_size" type="number" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('party_size')" class="mt-2" />
                        </div>

                        <!-- Date -->
                        <div>
                            <x-input-label for="reservation_date" :value="__('Date')" />
                            <x-text-input id="reservation_date" name="reservation_date" type="date" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                        </div>

                        <!-- Time -->
                        <div>
                            <x-input-label for="reservation_time" :value="__('Time')" />
                            <x-text-input id="reservation_time" name="reservation_time" type="time" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('reservation_time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <x-secondary-button x-on:click="window.history.back()" type="button" class="mr-3">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-primary-button>
                            {{ __('Save Reservation') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
