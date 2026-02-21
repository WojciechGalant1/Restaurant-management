<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Edit Room') . ': ' . $room->name">
            <x-slot name="action">
                <a href="{{ route('rooms.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('rooms.update', $room) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $room->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $room->description)" />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="color" :value="__('Color')" />
                            <input id="color" name="color" type="color" value="{{ old('color', $room->color) }}" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm cursor-pointer" />
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Room') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
