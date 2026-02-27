<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Table {{ $order->table->table_number }} — {{ __('Active Order') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('Guests') }}: {{ $guests ?? '-' }} • {{ __('Waiter') }}: {{ $order->waiter?->name ?? '-' }} • {{ __('Opened') }}: {{ $order->ordered_at->format('H:i') }}
            </p>
        </div>
    </x-slot>

    @php
        $itemsForEdit = $order->orderItems->map(fn ($i) => [
            'id' => $i->id,
            'menu_item_id' => (int) $i->menu_item_id,
            'quantity' => $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'notes' => $i->notes ?? '',
            'status' => $i->status->value,
            'cancel_action' => in_array($i->status, [\App\Enums\OrderItemStatus::Voided, \App\Enums\OrderItemStatus::Cancelled]) ? $i->status->value : null,
            'cancel_reason' => $i->cancellation_reason ?? '',
        ])->values();
        $menuLookup = $menuItems->keyBy('id')->map(fn ($m) => ['name' => $m->dish?->name ?? '?', 'price' => (float) $m->price]);
    @endphp

    <div class="py-6" x-data="{
        items: @js($itemsForEdit),
        menuLookup: @js($menuLookup),
        addMenuItemId: null,
        addQuantity: 1,
        addNotes: '',
        cancelModalOpen: false,
        pendingCancelIndex: null,
        pendingCancelAction: null,
        pendingCancelReason: '',
        getItemName(menuItemId) {
            return this.menuLookup[menuItemId]?.name ?? '?';
        },
        getItemPrice(menuItemId) {
            return this.menuLookup[menuItemId]?.price ?? 0;
        },
        addItem() {
            if (!this.addMenuItemId) return;
            const price = this.getItemPrice(this.addMenuItemId);
            this.items.push({
                id: null,
                menu_item_id: parseInt(this.addMenuItemId),
                quantity: parseInt(this.addQuantity) || 1,
                unit_price: price,
                notes: (this.addNotes || '').trim(),
                status: '{{ \App\Enums\OrderItemStatus::Pending->value }}',
                cancel_action: null,
                cancel_reason: ''
            });
            this.addMenuItemId = null;
            this.addQuantity = 1;
            this.addNotes = '';
        },
        removeItem(index) {
            if (this.items[index].id) return;
            this.items.splice(index, 1);
        },
        openCancelModal(index, action) {
            this.pendingCancelIndex = index;
            this.pendingCancelAction = action;
            this.pendingCancelReason = this.items[index].cancel_reason || '';
            this.cancelModalOpen = true;
        },
        confirmCancel() {
            if (!this.pendingCancelReason.trim()) return;
            this.items[this.pendingCancelIndex].cancel_action = this.pendingCancelAction;
            this.items[this.pendingCancelIndex].cancel_reason = this.pendingCancelReason.trim();
            this.cancelModalOpen = false;
            this.pendingCancelIndex = null;
            this.pendingCancelAction = null;
            this.pendingCancelReason = '';
        },
        closeCancelModal() {
            this.cancelModalOpen = false;
            this.pendingCancelIndex = null;
            this.pendingCancelAction = null;
            this.pendingCancelReason = '';
        },
        isTerminal(item) {
            return ['{{ \App\Enums\OrderItemStatus::Cancelled->value }}', '{{ \App\Enums\OrderItemStatus::Voided->value }}'].includes(item.cancel_action || item.status);
        },
        get total() {
            return this.items
                .filter(item => !this.isTerminal(item))
                .reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
                .toFixed(2);
        },
        validateBeforeSubmit() {
            const missing = this.items.find(item => (item.cancel_action === 'voided' || item.cancel_action === 'cancelled') && !(item.cancel_reason || '').trim());
            if (missing) {
                alert('{{ __('Reason is required for voided or cancelled items.') }}');
                return false;
            }
            return true;
        },
        statusBadgeClass(status) {
            const s = status || '';
            if (s === 'pending') return 'bg-gray-200 text-gray-800';
            if (s === 'preparing') return 'bg-yellow-200 text-yellow-900';
            if (s === 'ready') return 'bg-blue-200 text-blue-900';
            if (s === 'served') return 'bg-green-200 text-green-900';
            if (s === 'cancelled') return 'bg-red-200 text-red-900';
            if (s === 'voided') return 'bg-gray-200 text-gray-800';
            return 'bg-gray-200 text-gray-800';
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('orders.update', $order) }}" @submit="if (!validateBeforeSubmit()) $event.preventDefault()">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Left: Add items panel (2/3) --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Add items') }}</h3>
                            <div class="flex flex-wrap gap-4 items-end">
                                <div class="flex-1 min-w-[200px]">
                                    <x-input-label for="add_dish" :value="__('Dish')" />
                                    <select id="add_dish" x-model="addMenuItemId"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">{{ __('Select a dish') }}</option>
                                        @foreach($menuItemsGrouped as $category => $items)
                                            <optgroup label="{{ \App\Enums\DishCategory::tryFrom($category)?->label() ?? ucfirst($category) }}">
                                                @foreach($items as $menuItem)
                                                    <option value="{{ $menuItem->id }}">{{ $menuItem->dish?->name }} ({{ number_format($menuItem->price, 2) }} PLN)</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <x-input-label for="add_qty" :value="__('Qty')" />
                                    <x-text-input id="add_qty" type="number" x-model.number="addQuantity" min="1" class="mt-1 block w-full" />
                                </div>
                                <div class="flex-1 min-w-[150px]">
                                    <x-input-label for="add_notes" :value="__('Notes')" />
                                    <x-text-input id="add_notes" type="text" x-model="addNotes" class="mt-1 block w-full" placeholder="{{ __('Optional') }}" />
                                </div>
                                <button type="button" @click="addItem()" :disabled="!addMenuItemId"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    {{ __('Add') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Order summary (1/3) --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-24 flex flex-col max-h-[calc(100vh-8rem)]">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Order') }}</h3>
                            <div class="flex-1 overflow-y-auto space-y-3 min-h-0">
                                <template x-for="(item, index) in items" :key="item.id ?? 'new-' + index">
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <input type="hidden" :name="'items['+index+'][id]'" :value="item.id || ''">
                                        <input type="hidden" :name="'items['+index+'][menu_item_id]'" :value="item.menu_item_id">
                                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                        <input type="hidden" :name="'items['+index+'][unit_price]'" :value="item.unit_price">
                                        <input type="hidden" :name="'items['+index+'][notes]'" :value="item.notes || ''">
                                        <input type="hidden" :name="'items['+index+'][cancel_action]'" :value="item.cancel_action || ''">
                                        <input type="hidden" :name="'items['+index+'][cancel_reason]'" :value="item.cancel_reason || ''">

                                        <div class="flex justify-between items-start gap-2">
                                            <div class="min-w-0 flex-1">
                                                <span class="font-medium" x-text="item.quantity + 'x ' + getItemName(item.menu_item_id)"></span>
                                                <span class="text-gray-600" x-text="' ' + (item.quantity * item.unit_price).toFixed(2) + ' PLN'"></span>
                                            </div>
                                            <template x-if="!item.id">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 shrink-0" title="{{ __('Remove unsaved item') }}">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </template>
                                        </div>
                                        <p x-show="item.notes" x-text="item.notes" class="text-sm text-gray-500 mt-1 truncate" :title="item.notes"></p>
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium cursor-help"
                                                :class="statusBadgeClass(item.cancel_action || item.status)"
                                                x-text="item.cancel_action || item.status"
                                                x-effect="if (item.cancel_reason && window.tippy) { $nextTick(() => { if ($el._tippy) $el._tippy.destroy(); $el._tippy = window.tippy($el, { content: item.cancel_reason, placement: 'top', theme: 'light-border' }) }); return () => { if ($el._tippy) { $el._tippy.destroy(); $el._tippy = null } } }"></span>
                                            <template x-if="item.id && item.status !== '{{ \App\Enums\OrderItemStatus::Served->value }}' && item.status !== '{{ \App\Enums\OrderItemStatus::Cancelled->value }}' && item.status !== '{{ \App\Enums\OrderItemStatus::Voided->value }}' && !item.cancel_action">
                                                <div class="flex gap-1">
                                                    <button type="button" @click="openCancelModal(index, 'voided')"
                                                        class="text-xs px-2 py-0.5 bg-amber-100 text-amber-800 rounded hover:bg-amber-200"
                                                        title="{{ __('Void (mistake before kitchen)') }}">{{ __('Void') }}</button>
                                                    <button type="button" @click="openCancelModal(index, 'cancelled')"
                                                        class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded hover:bg-red-200"
                                                        title="{{ __('Cancel (after sent)') }}">{{ __('Cancel') }}</button>
                                                </div>
                                            </template>
                                            <template x-if="item.cancel_action && !['{{ \App\Enums\OrderItemStatus::Cancelled->value }}', '{{ \App\Enums\OrderItemStatus::Voided->value }}'].includes(item.status)">
                                                <button type="button" @click="item.cancel_action = null; item.cancel_reason = ''"
                                                    class="text-xs px-2 py-0.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">{{ __('Undo') }}</button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 pt-4 border-t shrink-0">
                                <div class="text-xl font-bold text-gray-900">
                                    {{ __('Total') }}: <span x-text="total"></span> PLN
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        {{ __('Save & go to order details') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Modal: Reason for void/cancel --}}
        <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeCancelModal()">
            <div x-show="cancelModalOpen" x-transition class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Reason required') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Please provide a reason for this action (audit trail).') }}</p>
                <textarea x-model="pendingCancelReason" rows="3" placeholder="{{ __('e.g. Wrong order, guest changed mind...') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="closeCancelModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" @click="confirmCancel()" :disabled="!pendingCancelReason.trim()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
