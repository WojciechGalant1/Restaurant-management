<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Dish') }}: {{ $dish->name }}
            </h2>
            <a href="{{ route('dishes.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('dishes.update', $dish) }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <x-input-label for="name" :value="__('Dish Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $dish->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('description', $dish->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category" :value="__('Category')" />
                             <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @php $current = old('category', $dish->category instanceof \App\Enums\DishCategory ? $dish->category->value : $dish->category); @endphp
                                <option value="{{ \App\Enums\DishCategory::Starter->value }}" {{ $current === \App\Enums\DishCategory::Starter->value ? 'selected' : '' }}>Starter</option>
                                <option value="{{ \App\Enums\DishCategory::Main->value }}" {{ $current === \App\Enums\DishCategory::Main->value ? 'selected' : '' }}>Main Course</option>
                                <option value="{{ \App\Enums\DishCategory::Dessert->value }}" {{ $current === \App\Enums\DishCategory::Dessert->value ? 'selected' : '' }}>Dessert</option>
                                <option value="{{ \App\Enums\DishCategory::Drink->value }}" {{ $current === \App\Enums\DishCategory::Drink->value ? 'selected' : '' }}>Drink</option>
                                <option value="{{ \App\Enums\DishCategory::Side->value }}" {{ $current === \App\Enums\DishCategory::Side->value ? 'selected' : '' }}>Side Dish</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Dish') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
