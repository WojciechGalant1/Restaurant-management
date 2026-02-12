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

    <div class="py-12" x-data="kitchenDisplay(@js($items->map(fn($item) => [
        'id' => $item->id,
        'order_id' => $item->order_id,
        'table_number' => $item->order->table->table_number ?? 'N/A',
        'name' => $item->menuItem->dish->name,
        'quantity' => $item->quantity,
        'notes' => $item->notes,
        'status' => $item->status,
        'created_at_human' => $item->created_at->diffForHumans(),
        'updated_at_human' => $item->updated_at->diffForHumans(),
        'update_url' => route('kitchen.update-status', $item->id),
    ])))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column: New Orders (Pending) -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-red-600 flex items-center">
                        <x-heroicon-o-fire class="w-5 h-5 mr-2" />
                        {{ __('New Orders') }}
                    </h3>
                    <div class="space-y-4">
                        <template x-for="item in items.filter(i => i.status === 'pending')" :key="item.id">
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500 animate-fade-in-down">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.created_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2" x-text="`${item.quantity}x ${item.name}`"></div>
                                <template x-if="item.notes">
                                    <div class="bg-yellow-50 p-2 rounded text-sm text-yellow-800 mb-4 italic" x-text="`&quot;${item.notes}&quot;`"></div>
                                </template>
                                <form :action="item.update_url" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 transition font-bold">
                                        {{ __('Start Preparing') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === 'pending').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                            {{ __('No new orders.') }}
                        </div>
                    </div>
                </div>

                <!-- Column: Currently Preparing -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-orange-600 flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                        {{ __('Preparing') }}
                    </h3>
                    <div class="space-y-4">
                        <template x-for="item in items.filter(i => i.status === 'preparing')" :key="item.id">
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-orange-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2" x-text="`${item.quantity}x ${item.name}`"></div>
                                <form :action="item.update_url" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 transition font-bold">
                                        {{ __('Mark as Ready') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === 'preparing').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                            {{ __('Nothing being prepared.') }}
                        </div>
                    </div>
                </div>

                <!-- Column: Ready to Serve -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-green-600 flex items-center">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                        {{ __('Ready to Serve') }}
                    </h3>
                    <div class="space-y-4">
                        <template x-for="item in items.filter(i => i.status === 'ready')" :key="item.id">
                            <div class="bg-green-50 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2 italic line-through" x-text="`${item.quantity}x ${item.name}`"></div>
                                <div class="text-center font-bold text-green-700">
                                    {{ __('Wait for Staff pickup') }}
                                </div>
                                <form :action="item.update_url" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-1 rounded-md hover:bg-gray-300 transition text-xs">
                                        {{ __('Move back to Preparing') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === 'ready').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                            {{ __('No items ready.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kitchenDisplay', (initialItems) => ({
                items: initialItems,

                init() {
                    if (window.Echo) {
                        window.Echo.private('kitchen')
                            .listen('OrderItemCreated', (e) => {
                                // Add logic to avoid duplicates just in case
                                if (!this.items.find(i => i.id === e.id)) {
                                    // Construct the update URL
                                    // We can use a template or assume the structure
                                    const updateUrl = "{{ route('kitchen.update-status', ':id') }}".replace(':id', e.id);
                                    this.items.unshift({ ...e, update_url: updateUrl });
                                }
                            })
                            .listen('OrderItemStatusUpdated', (e) => {
                                const index = this.items.findIndex(i => i.id === e.id);
                                if (index !== -1) {
                                    if (e.status === 'served') {
                                        this.items.splice(index, 1);
                                    } else {
                                        this.items[index].status = e.status;
                                        this.items[index].updated_at_human = e.updated_at_human;
                                    }
                                }
                            });
                    }
                }
            }));
        });
    </script>

    <style>
        .animate-fade-in-down {
            animation: fadeInDown 0.5s ease-out;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>
