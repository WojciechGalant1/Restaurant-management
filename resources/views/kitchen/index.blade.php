<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kitchen Display System') }}
            </h2>
            <div class="flex items-center text-sm text-gray-500" x-data>
                <span x-bind:class="$store.echo.connected ? 'bg-green-500' : 'bg-red-500'" 
                      class="mr-2 animate-pulse rounded-full h-2 w-2"></span>
                <span x-text="$store.echo.connected ? '{{ __('Live Updates') }}' : '{{ __('Connecting...') }}'"></span>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="kitchenDisplay(@js($items->map(fn($item) => [
        'id' => $item->id,
        'order_id' => $item->order_id,
        'table_number' => $item->order->table->table_number ?? 'N/A',
        'name' => $item->menuItem->dish->name,
        'quantity' => $item->quantity,
        'notes' => $item->notes,
        'status' => $item->status->value,
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
                        <template x-for="item in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Pending->value }}')" :key="item.id">
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500 animate-fade-in-down">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.created_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2" x-text="`${item.quantity}x ${item.name}`"></div>
                                <template x-if="item.notes">
                                    <div class="bg-yellow-50 p-2 rounded text-sm text-yellow-800 mb-4 italic" x-text="`&quot;${item.notes}&quot;`"></div>
                                </template>
                                <form @submit.prevent="updateStatus(item, '{{ \App\Enums\OrderItemStatus::Preparing->value }}')">
                                    @csrf
                                    <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-md hover:bg-orange-600 transition font-bold">
                                        {{ __('Start In Preparation') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Pending->value }}').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                            {{ __('No new orders.') }}
                        </div>
                    </div>
                </div>

                <!-- Column: Currently In Preparation -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-orange-600 flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                        {{ __('In Preparation') }}
                    </h3>
                    <div class="space-y-4">
                        <template x-for="item in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Preparing->value }}')" :key="item.id">
                            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-orange-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2" x-text="`${item.quantity}x ${item.name}`"></div>
                                <form @submit.prevent="updateStatus(item, '{{ \App\Enums\OrderItemStatus::Ready->value }}')">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 transition font-bold">
                                        {{ __('Mark as Ready') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Preparing->value }}').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
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
                        <template x-for="item in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}')" :key="item.id">
                            <div class="bg-green-50 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-sm" x-text="`#${item.order_id} - Table ${item.table_number}`"></span>
                                    <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 mb-2 italic line-through" x-text="`${item.quantity}x ${item.name}`"></div>
                                <div class="text-center font-bold text-green-700">
                                    {{ __('Wait for Staff pickup') }}
                                </div>
                                <form @submit.prevent="updateStatus(item, '{{ \App\Enums\OrderItemStatus::Preparing->value }}')" class="mt-2">
                                    @csrf
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-1 rounded-md hover:bg-gray-300 transition text-xs">
                                        {{ __('Move back to In Preparation') }}
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length === 0" class="text-gray-500 italic text-sm text-center py-4 bg-gray-50 rounded-lg">
                            {{ __('No items ready.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            // Global store for Echo connection status
            Alpine.store('echo', {
                connected: false
            });

            Alpine.data('kitchenDisplay', (initialItems) => ({
                items: initialItems,

                init() {
                    this.setupEcho();
                },

                async updateStatus(item, newStatus) {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (!csrf) return;
                    try {
                        const res = await fetch(item.update_url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });
                        if (!res.ok) throw new Error('Request failed');
                        const idx = this.items.findIndex(i => i.id === item.id);
                        if (idx !== -1) {
                            this.items[idx] = { ...this.items[idx], status: newStatus, updated_at_human: '{{ __("just now") }}' };
                        }
                    } catch (e) {
                        console.error(e);
                    }
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
                        const channel = window.Echo.private('kitchen');
                        
                        channel
                            .listen('.OrderItemCreated', (e) => {
                                console.log('OrderItemCreated event received:', e);
                                // Check if item already exists (avoid duplicates)
                                if (!this.items.find(i => i.id === e.id)) {
                                    this.items.unshift(e);
                                }
                            })
                            .listen('.OrderItemStatusUpdated', (e) => {
                                console.log('OrderItemStatusUpdated event received:', e);
                                const index = this.items.findIndex(i => i.id === e.id);
                                if (index !== -1) {
                                    if (e.status === '{{ \App\Enums\OrderItemStatus::Served->value }}' || e.status === '{{ \App\Enums\OrderItemStatus::Cancelled->value }}') {
                                        this.items = this.items.filter(i => i.id !== e.id);
                                    } else {
                                        // Update existing item with new data
                                        this.items[index] = { ...this.items[index], ...e };
                                    }
                                    } else if (e.status !== '{{ \App\Enums\OrderItemStatus::Served->value }}' && e.status !== '{{ \App\Enums\OrderItemStatus::Cancelled->value }}') {
                                    // If item doesn't exist and status is not served, add it
                                    // This handles cases where item was created before page load
                                    this.items.unshift(e);
                                }
                            });

                        // Listen for connection events
                        window.Echo.connector.pusher.connection.bind('connected', () => {
                            console.log('Echo connected to Reverb');
                            Alpine.store('echo').connected = true;
                        });

                        window.Echo.connector.pusher.connection.bind('disconnected', () => {
                            console.log('Echo disconnected from Reverb');
                            Alpine.store('echo').connected = false;
                        });

                        // Set initial connection status
                        Alpine.store('echo').connected = true;
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
