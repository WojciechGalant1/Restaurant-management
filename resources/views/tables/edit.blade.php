<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Table') }} #{{ $table->table_number }}
            </h2>
            <a href="{{ route('tables.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tables.update', $table) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="table_number" :value="__('Table Number')" />
                            <x-text-input id="table_number" name="table_number" type="number" class="mt-1 block w-full bg-gray-100" :value="$table->table_number" readonly />
                            <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Table number cannot be changed.</p>
                        </div>

                        <div>
                            <x-input-label for="capacity" :value="__('Capacity')" />
                            <x-text-input id="capacity" name="capacity" type="number" class="mt-1 block w-full" :value="old('capacity', $table->capacity)" required />
                            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="available" {{ old('status', $table->status) === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ old('status', $table->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="reserved" {{ old('status', $table->status) === 'reserved' ? 'selected' : '' }}>Reserved</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Table') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
