<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Order') }} #{{ $order->id }}
        </h2>
    </x-slot>

    @php
        $itemsForEdit = $order->orderItems->map(fn ($i) => [
            'id' => $i->id,
            'menu_item_id' => (int) $i->menu_item_id,
            'quantity' => $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'notes' => $i->notes ?? '',
        ])->values();
    @endphp
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('orders.update', $order) }}" x-data="{
                    items: @js($itemsForEdit),
                    menuItems: @js($menuItems),
                    addItem() {
                        this.items.push({ id: null, menu_item_id: null, quantity: 1, unit_price: 0, notes: '' });
                    },
                    removeItem(index) {
                        this.items.splice(index, 1);
                    },
                    updatePrice(index) {
                        const selectedId = this.items[index].menu_item_id;
                        const menuItem = this.menuItems.find(i => i.id == selectedId);
                        if (menuItem) {
                            this.items[index].unit_price = menuItem.price;
                        }
                    },
                    get total() {
                        return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0).toFixed(2);
                    }
                }">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-8">
                        <x-input-label for="table_id" :value="__('Table')" />
                        <select id="table_id" name="table_id" class="mt-1 block w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" @selected($order->table_id === $table->id)>Table #{{ $table->table_number }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('table_id')" class="mt-2" />
                    </div>

                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Order Items') }}</h3>
                        <button type="button" @click="addItem()" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            + Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.id ?? 'new-' + index">
                            <div class="flex items-end space-x-4 p-4 bg-gray-50 rounded-lg">
                                <template x-if="item.id">
                                    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                </template>
                                <div class="flex-grow">
                                    <x-input-label :value="__('Dish')" />
                                    <select
                                        :name="'items['+index+'][menu_item_id]'"
                                        x-model.number="item.menu_item_id"
                                        @change="updatePrice(index)"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">Select a dish</option>
                                        @foreach($menuItems as $menuItem)
                                            <option value="{{ $menuItem->id }}">{{ $menuItem->dish->name }} ({{ number_format($menuItem->price, 2) }} PLN)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <x-input-label :value="__('Qty')" />
                                    <x-text-input type="number" x-bind:name="'items['+index+'][quantity]'" x-model="item.quantity" min="1" class="mt-1 block w-full" required />
                                </div>
                                <div class="w-32">
                                    <x-input-label :value="__('Price')" />
                                    <div class="mt-2 text-sm font-bold">
                                        <span x-text="(item.quantity * item.unit_price).toFixed(2)"></span> PLN
                                        <input type="hidden" x-bind:name="'items['+index+'][unit_price]'" :value="item.unit_price">
                                    </div>
                                </div>
                                <div class="w-40">
                                    <x-input-label :value="__('Notes')" />
                                    <x-text-input type="text" x-bind:name="'items['+index+'][notes]'" x-model="item.notes" class="mt-1 block w-full" placeholder="Optional" />
                                </div>
                                <div>
                                    <button type="button" @click="removeItem(index)" class="mb-1 text-red-600 hover:text-red-900">
                                        <x-heroicon-o-trash class="w-6 h-6" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-8 pt-6 border-t flex justify-between items-center">
                        <div class="text-2xl font-extrabold text-gray-900 font-mono">
                            Total: <span x-text="total"></span> PLN
                        </div>
                        <div class="flex space-x-4">
                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save changes') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
