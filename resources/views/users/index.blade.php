<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Staff Management')" :actionUrl="route('users.create')" :actionLabel="__('Add New Staff')" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message type="success" />
            <x-flash-message type="error" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $user->role === \App\Enums\UserRole::Manager ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $user->role === \App\Enums\UserRole::Chef ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $user->role === \App\Enums\UserRole::Waiter ? 'bg-blue-100 text-blue-800' : '' }}">
                                            {{ ucfirst($user->role->value) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                            <x-heroicon-o-pencil class="w-5 h-5" />
                                        </a>
                                        <x-delete-button :route="route('users.destroy', $user)" :confirmMessage="__('Are you sure you want to remove this staff member?')" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                        {{ __('No staff members found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
