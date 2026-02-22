<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Edit Staff Member') . ': ' . $user->first_name . ' ' . $user->last_name">
            <x-slot name="action">
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="first_name" :value="__('First Name')" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="last_name" :value="__('Last Name')" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Email Address (Read Only)')" />
                            <x-text-input type="email" class="mt-1 block w-full bg-gray-100" :value="$user->email" readonly />
                        </div>

                        <div>
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" />
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                             <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required autocomplete="off">
                                <option value="{{ \App\Enums\UserRole::Waiter->value }}" {{ old('role', $user->role->value) === \App\Enums\UserRole::Waiter->value ? 'selected' : '' }}>Waiter</option>
                                <option value="{{ \App\Enums\UserRole::Chef->value }}" {{ old('role', $user->role->value) === \App\Enums\UserRole::Chef->value ? 'selected' : '' }}>Chef</option>
                                <option value="{{ \App\Enums\UserRole::Manager->value }}" {{ old('role', $user->role->value) === \App\Enums\UserRole::Manager->value ? 'selected' : '' }}>Manager</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Staff Member') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
