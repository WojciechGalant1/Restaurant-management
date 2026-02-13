<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Waiter Display - Ready Items') }}
            </h2>
            <div class="flex items-center text-sm text-gray-500" x-data>
                <span x-bind:class="$store.echo.connected ? 'bg-green-500' : 'bg-red-500'" 
                      class="mr-2 animate-pulse rounded-full h-2 w-2"></span>
                <span x-text="$store.echo.connected ? '{{ __('Live Updates') }}' : '{{ __('Connecting...') }}'"></span>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="waiterDisplay(@js($items->map(fn($item) => [
        'id' => $item->id,
        'order_id' => $item->order_id,
        'table_number' => $item->order->table->table_number ?? 'N/A',
        'name' => $item->menuItem->dish->name,
        'quantity' => $item->quantity,
        'notes' => $item->notes,
        'status' => $item->status,
        'unit_price' => $item->unit_price,
        'total_price' => number_format($item->quantity * $item->unit_price, 2),
        'created_at_human' => $item->created_at->diffForHumans(),
        'updated_at_human' => $item->updated_at->diffForHumans(),
        'mark_served_url' => route('waiter.mark-served', $item->id),
    ])))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ __('Items Ready to Serve') }}
                        </h3>
                        <span class="text-sm text-gray-500" x-text="`${items.filter(i => i.status === 'ready').length} {{ __('items') }}`"></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in items.filter(i => i.status === 'ready')" :key="item.id">
                    <div class="bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm p-6 animate-fade-in-down">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="font-bold text-lg text-gray-900" x-text="`Table ${item.table_number}`"></span>
                                <span class="text-sm text-gray-600 ml-2" x-text="`Order #${item.order_id}`"></span>
                            </div>
                            <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="text-xl font-bold text-gray-900 mb-1" x-text="`${item.quantity}x ${item.name}`"></div>
                            <div class="text-sm text-gray-600" x-text="`{{ __('Unit Price') }}: ${item.unit_price} PLN`"></div>
                            <div class="text-sm font-semibold text-gray-800" x-text="`{{ __('Total') }}: ${item.total_price} PLN`"></div>
                        </div>

                        <template x-if="item.notes">
                            <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-4">
                                <p class="text-sm text-yellow-800 italic" x-text="`&quot;${item.notes}&quot;`"></p>
                            </div>
                        </template>

                        <form :action="item.mark_served_url" method="POST" class="mt-4">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                {{ __('Mark as Served') }}
                            </button>
                        </form>
                    </div>
                </template>
            </div>

            <div x-show="items.filter(i => i.status === 'ready').length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                <x-heroicon-o-check-circle class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 text-lg">{{ __('No items ready to serve.') }}</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            // Use existing Echo store if available, otherwise create it
            if (!Alpine.store('echo')) {
                Alpine.store('echo', {
                    connected: false
                });
            }

            Alpine.data('waiterDisplay', (initialItems) => ({
                items: initialItems,

                init() {
                    // Wait for Echo to be available
                    this.setupEcho();
                },

                setupEcho() {
                    // Check if Echo is available, if not wait a bit
                    if (window.Echo) {
                        this.connectEcho();
                    } else {
                        // Retry after a short delay
                        setTimeout(() => this.setupEcho(), 100);
                    }
                },

                connectEcho() {
                    try {
                        // Use private channel for waiters (same as kitchen)
                        const channel = window.Echo.private('kitchen');
                        
                        channel
                            .listen('.OrderItemStatusUpdated', (e) => {
                                console.log('OrderItemStatusUpdated event received:', e);
                                const index = this.items.findIndex(i => i.id === e.id);
                                
                                if (e.status === 'ready') {
                                    // Add item if it's ready and doesn't exist
                                    if (index === -1) {
                                        // Construct the mark served URL
                                        const markServedUrl = "{{ route('waiter.mark-served', ':id') }}".replace(':id', e.id);
                                        this.items.unshift({
                                            ...e,
                                            total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                            mark_served_url: markServedUrl
                                        });
                                    } else {
                                        // Update existing item
                                        this.items[index] = { ...this.items[index], ...e };
                                    }
                                } else if (e.status === 'served' || e.status !== 'ready') {
                                    // Remove item if it's served or no longer ready
                                    if (index !== -1) {
                                        this.items.splice(index, 1);
                                    }
                                }
                            })
                            .listen('.OrderItemCreated', (e) => {
                                console.log('OrderItemCreated event received:', e);
                                // Only add if status is ready (shouldn't happen often, but handle it)
                                if (e.status === 'ready' && !this.items.find(i => i.id === e.id)) {
                                    const markServedUrl = "{{ route('waiter.mark-served', ':id') }}".replace(':id', e.id);
                                    this.items.unshift({
                                        ...e,
                                        total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                        mark_served_url: markServedUrl
                                    });
                                }
                            });

                        // Listen for connection events
                        if (window.Echo.connector && window.Echo.connector.pusher) {
                            window.Echo.connector.pusher.connection.bind('connected', () => {
                                console.log('Echo connected to Reverb (Waiter)');
                                Alpine.store('echo').connected = true;
                            });

                            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                                console.log('Echo disconnected from Reverb (Waiter)');
                                Alpine.store('echo').connected = false;
                            });

                            // Set initial connection status
                            Alpine.store('echo').connected = true;
                        }
                    } catch (error) {
                        console.error('Error setting up Echo:', error);
                        Alpine.store('echo').connected = false;
                        // Retry after delay
                        setTimeout(() => this.setupEcho(), 1000);
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
