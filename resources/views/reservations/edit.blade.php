<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Reservation') }}: {{ $reservation->customer_name }}
            </h2>
            <a href="{{ route('reservations.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('reservations.update', $reservation) }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="customer_name" :value="__('Customer Name')" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name', $reservation->customer_name)" required autofocus />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $reservation->phone_number)" required />
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="table_id" :value="__('Assigned Table')" />
                            <select id="table_id" name="table_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}" {{ old('table_id', $reservation->table_id) == $table->id ? 'selected' : '' }}>
                                        Table #{{ $table->table_number }} ({{ $table->capacity }} guests)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('table_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reservation_date" :value="__('Date')" />
                            <x-text-input id="reservation_date" name="reservation_date" type="date" class="mt-1 block w-full" :value="old('reservation_date', $reservation->reservation_date)" required />
                            <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="party_size" :value="__('Number of Guests')" />
                            <x-text-input id="party_size" name="party_size" type="number" min="1" class="mt-1 block w-full" :value="old('party_size', $reservation->party_size)" required />
                            <x-input-error :messages="$errors->get('party_size')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Reservation') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
