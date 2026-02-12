<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kitchen Display System') }}
            </h2>
            <div class="flex items-center text-sm text-gray-500">
                <span class="mr-2 animate-pulse rounded-full h-2 w-2 bg-green-500"></span>
                {{ __('Live Updates (Refreshes every 10s)') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column: New Orders (Pending) -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-red-600 flex items-center">
                        <x-heroicon-o-fire class="w-5 h-5 mr-2" />
                        {{ __('New Orders') }}
                    </h3>
                    <div class="space-y-4">
                        @forelse($items->where('status', 'pending') as $item)
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm">#{{ $item->order_id }} - Table {{ $item->order->table->table_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2">
                                    {{ $item->quantity }}x {{ $item->menuItem->dish->name }}
                                </div>
                                @if($item->notes)
                                    <div class="bg-yellow-50 p-2 rounded text-sm text-yellow-800 mb-4 italic">
                                        "{{ $item->notes }}"
                                    </div>
                                @endif
                                <form action="{{ route('kitchen.update-status', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 transition font-bold">
                                        {{ __('Start Preparing') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                                {{ __('No new orders.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column: Currently Preparing -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-orange-600 flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                        {{ __('Preparing') }}
                    </h3>
                    <div class="space-y-4">
                        @forelse($items->where('status', 'preparing') as $item)
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-orange-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm">#{{ $item->order_id }} - Table {{ $item->order->table->table_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ $item->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2">
                                    {{ $item->quantity }}x {{ $item->menuItem->dish->name }}
                                </div>
                                <form action="{{ route('kitchen.update-status', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 transition font-bold">
                                        {{ __('Mark as Ready') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                                {{ __('Nothing being prepared.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column: Ready to Serve -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-green-600 flex items-center">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                        {{ __('Ready to Serve') }}
                    </h3>
                    <div class="space-y-4">
                        @forelse($items->where('status', 'ready') as $item)
                            <div class="bg-green-50 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm">#{{ $item->order_id }} - Table {{ $item->order->table->table_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ $item->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2 italic line-through">
                                    {{ $item->quantity }}x {{ $item->menuItem->dish->name }}
                                </div>
                                <div class="text-center font-bold text-green-700">
                                    {{ __('Wait for Staff pickup') }}
                                </div>
                                <form action="{{ route('kitchen.update-status', $item) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-1 rounded-md hover:bg-gray-300 transition text-xs">
                                        {{ __('Move back to Preparing') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                                {{ __('No items ready.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            location.reload();
        }, 10000);
    </script>
</x-app-layout>
