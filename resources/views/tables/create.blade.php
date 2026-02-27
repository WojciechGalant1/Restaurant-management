<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Add New Table')">
            <x-slot name="action">
                <a href="{{ route('tables.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tables.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <x-input-label for="table_number" :value="__('Table Number')" />
                            <x-text-input id="table_number" name="table_number" type="number" class="mt-1 block w-full" :value="old('table_number')" required />
                            <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="capacity" :value="__('Capacity')" />
                            <x-text-input id="capacity" name="capacity" type="number" class="mt-1 block w-full" :value="old('capacity')" required />
                            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="{{ \App\Enums\TableStatus::Available->value }}" {{ old('status') === \App\Enums\TableStatus::Available->value ? 'selected' : '' }}>Available</option>
                                <option value="{{ \App\Enums\TableStatus::Occupied->value }}" {{ old('status') === \App\Enums\TableStatus::Occupied->value ? 'selected' : '' }}>Occupied</option>
                                <option value="{{ \App\Enums\TableStatus::Cleaning->value }}" {{ old('status') === \App\Enums\TableStatus::Cleaning->value ? 'selected' : '' }}>Cleaning</option>
                                <option value="{{ \App\Enums\TableStatus::Reserved->value }}" {{ old('status') === \App\Enums\TableStatus::Reserved->value ? 'selected' : '' }}>Reserved</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="room_id" :value="__('Room')" />
                            <select id="room_id" name="room_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('— No room —') }}</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Add Table') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
