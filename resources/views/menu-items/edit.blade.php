<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Edit Menu Item') . ': ' . ($menuItem->dish->name ?? 'Unknown Dish')">
            <x-slot name="action">
                <a href="{{ route('menu-items.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('menu-items.update', $menuItem) }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label :value="__('Dish (Read Only)')" />
                            <x-text-input type="text" class="mt-1 block w-full bg-gray-100" :value="$menuItem->dish->name ?? 'N/A'" readonly />
                        </div>

                        <div>
                            <x-input-label for="price" :value="__('Price (PLN)')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" :value="old('price', $menuItem->price)" required autofocus />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="is_available" :value="__('Availability')" />
                            <select id="is_available" name="is_available" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="1" {{ old('is_available', $menuItem->is_available) == '1' ? 'selected' : '' }}>Available</option>
                                <option value="0" {{ old('is_available', $menuItem->is_available) == '0' ? 'selected' : '' }}>Unavailable</option>
                            </select>
                            <x-input-error :messages="$errors->get('is_available')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Menu Item') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
