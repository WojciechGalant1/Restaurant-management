<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Tables Management') }}
                </h2>
                @if(isset($currentUser) && $currentUser->role !== \App\Enums\UserRole::Manager)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('You are viewing tables assigned to you.') }}
                    </p>
                @endif
            </div>
            @if(isset($currentUser) && $currentUser->role === \App\Enums\UserRole::Manager)
                <a href="{{ route('tables.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Add New Table') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm sm:rounded-r-lg" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Tables List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('List of Tables') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waiter</th>
                                @if(isset($currentUser) && $currentUser->role === \App\Enums\UserRole::Manager)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($tables as $table)
                                @php
                                    $status = $table->status instanceof \App\Enums\TableStatus ? $table->status : \App\Enums\TableStatus::tryFrom($table->status);
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $table->table_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $table->capacity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $status === \App\Enums\TableStatus::Available ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Occupied ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Reserved ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ $status instanceof \App\Enums\TableStatus ? $status->label() : ucfirst($table->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $table->waiter?->name ?? __('Unassigned') }}
                                    </td>
                                    @if(isset($currentUser) && $currentUser->role === \App\Enums\UserRole::Manager)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2 items-center">
                                            <form action="{{ route('tables.update', $table) }}" method="POST" class="flex items-center space-x-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="waiter_id" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()" title="{{ __('Only waiters currently on shift') }}">
                                                    <option value="">{{ __('Unassigned') }}</option>
                                                    @foreach($waiters as $waiter)
                                                        <option value="{{ $waiter->id }}" @selected($table->waiter_id === $waiter->id)>
                                                            {{ $waiter->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                            <a href="{{ route('tables.edit', $table) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <form action="{{ route('tables.destroy', $table) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this table?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-1 rounded transition">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                        {{ __('No tables found. Click the button above to add your first table.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
