<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Waiter Display') }}
            </h2>
            <div class="flex items-center text-sm text-gray-500" x-data>
                <span x-bind:class="$store.echo.connected ? 'bg-green-500' : 'bg-red-500'"
                      class="mr-2 animate-pulse rounded-full h-2 w-2"></span>
                <span x-text="$store.echo.connected ? '{{ __('Live') }}' : '{{ __('Connecting...') }}'"></span>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="waiterDisplay()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- 1. Sticky alert: Ready to Serve --}}
            <div x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length > 0"
                 class="sticky top-4 z-10 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-lg p-4 animate-pulse-subtle">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-red-600 font-bold text-lg">🔔</span>
                        <h3 class="text-lg font-bold text-red-800">
                            <span x-text="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length"></span> {{ __('Ready to Serve') }}
                        </h3>
                    </div>
                    <a href="#ready-section" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                        {{ __('Go to items') }}
                    </a>
                </div>
                <p class="text-sm text-red-700 mt-1" x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length > 0">
                    <template x-for="(item, idx) in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').slice(0, 3)" :key="item.id">
                        <span><span x-text="`Table ${item.table_number} – #${item.order_id}`"></span><span x-show="idx < Math.min(2, items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length - 1)">, </span></span>
                    </template>
                </p>
            </div>

            {{-- 2. My Tables (grouped by status) --}}
            @if($tables->isNotEmpty())
                @php
                    $statusOrder = [
                        \App\Enums\TableStatus::Occupied->value,
                        \App\Enums\TableStatus::Cleaning->value,
                        \App\Enums\TableStatus::Reserved->value,
                        \App\Enums\TableStatus::Available->value,
                    ];
                    $statusLabels = [
                        \App\Enums\TableStatus::Occupied->value => __('Occupied'),
                        \App\Enums\TableStatus::Cleaning->value => __('Cleaning'),
                        \App\Enums\TableStatus::Reserved->value => __('Reserved'),
                        \App\Enums\TableStatus::Available->value => __('Available'),
                    ];
                    $tablesByStatus = $tables->groupBy(fn($t) => $t->status->value);
                @endphp
                <section>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('My Tables') }}</h3>
                    @foreach($statusOrder as $statusValue)
                        @php $groupTables = $tablesByStatus->get($statusValue, collect()); @endphp
                        @if($groupTables->isNotEmpty())
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full
                                        {{ $statusValue === \App\Enums\TableStatus::Occupied->value ? 'bg-red-500' : '' }}
                                        {{ $statusValue === \App\Enums\TableStatus::Cleaning->value ? 'bg-orange-500' : '' }}
                                        {{ $statusValue === \App\Enums\TableStatus::Reserved->value ? 'bg-yellow-500' : '' }}
                                        {{ $statusValue === \App\Enums\TableStatus::Available->value ? 'bg-green-500' : '' }}"></span>
                                    {{ $statusLabels[$statusValue] ?? ucfirst($statusValue) }} ({{ $groupTables->count() }})
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach($groupTables as $table)
                            @php
                                $status = $table->status instanceof \App\Enums\TableStatus ? $table->status : \App\Enums\TableStatus::tryFrom($table->status);
                                $activeOrder = $activeOrders->firstWhere('table_id', $table->id);
                                $reservation = $reservationsByTable->get($table->id)?->first();
                            @endphp
                            <div class="bg-white rounded-lg shadow border-l-4
                                {{ $status === \App\Enums\TableStatus::Occupied ? 'border-red-400' : '' }}
                                {{ $status === \App\Enums\TableStatus::Cleaning ? 'border-orange-400' : '' }}
                                {{ $status === \App\Enums\TableStatus::Reserved ? 'border-yellow-400' : '' }}
                                {{ $status === \App\Enums\TableStatus::Available ? 'border-green-400' : '' }}">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-bold text-gray-900">Table #{{ $table->table_number }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            {{ $status === \App\Enums\TableStatus::Available ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Occupied ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Cleaning ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Reserved ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ $status instanceof \App\Enums\TableStatus ? $status->label() : ucfirst($table->status) }}
                                        </span>
                                    </div>
                                    
                                    @if($reservation)
                                        <div class="mb-3 p-2 bg-blue-50 rounded border border-blue-200">
                                            <div class="flex justify-between items-start mb-1">
                                                <p class="text-xs font-semibold text-blue-800">
                                                    {{ __('Reservation') }}: {{ ucfirst($reservation->status->value) }}
                                                </p>
                                            </div>
                                            <p class="text-sm text-gray-700 mb-2">
                                                {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }} · {{ $reservation->party_size }} {{ __('guests') }}
                                            </p>
                                            @if($reservation->status === \App\Enums\ReservationStatus::Confirmed)
                                                <form action="{{ route('waiter.reservation.mark-seated', $reservation) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full py-1.5 px-2 rounded text-xs font-medium bg-green-600 hover:bg-green-700 text-white transition">
                                                        {{ __('Seat guests') }}
                                                    </button>
                                                </form>
                                            @else
                                                <p class="text-xs font-medium px-2 py-1 rounded
                                                    {{ $reservation->status === \App\Enums\ReservationStatus::Pending ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $reservation->status === \App\Enums\ReservationStatus::WalkInSeated ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $reservation->status === \App\Enums\ReservationStatus::Seated ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ in_array($reservation->status, [\App\Enums\ReservationStatus::Completed, \App\Enums\ReservationStatus::Cancelled, \App\Enums\ReservationStatus::NoShow]) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ ucfirst($reservation->status->value) }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <p class="text-xs text-gray-500 mb-2">{{ $table->capacity }} {{ __('capacity') }}</p>
                                    
                                    @if($activeOrder)
                                        <p class="text-sm font-semibold text-gray-700 mb-3">{{ __('Order') }} #{{ $activeOrder->id }}: {{ number_format($activeOrder->total_price, 2) }} PLN</p>
                                        <a href="{{ route('orders.show', $activeOrder) }}" class="block w-full text-center py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                                            {{ __('Open Order') }}
                                        </a>
                                    @elseif($status === \App\Enums\TableStatus::Cleaning)
                                        <form action="{{ route('tables.complete-cleaning', $table) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-2 px-4 rounded-lg text-sm font-medium bg-orange-600 hover:bg-orange-700 text-white transition">
                                                {{ __('Clear table') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </section>
            @endif

            {{-- 3. Ready to Serve (detailed) --}}
            <section id="ready-section">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Ready to Serve') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="item in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}')" :key="item.id">
                        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-gray-900" x-text="`Table ${item.table_number}`"></span>
                                <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mb-1" x-text="`${item.quantity}x ${item.name}`"></p>
                            <p class="text-sm text-gray-600 mb-3" x-text="`${item.total_price} PLN`"></p>
                            <form @submit.prevent="markServed(item)">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                                    {{ __('Mark as Delivered') }}
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
                <div x-show="items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}').length === 0" class="text-center py-8 bg-white rounded-lg shadow text-gray-500">
                    {{ __('No items ready to serve.') }}
                </div>
            </section>

            {{-- 4. Active Orders --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Active Orders') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($activeOrders as $order)
                        <div class="bg-white rounded-lg shadow border p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-gray-900">#{{ $order->id }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $order->status === \App\Enums\OrderStatus::Open ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $order->status === \App\Enums\OrderStatus::Paid ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === \App\Enums\OrderStatus::Cancelled ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status->value)) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">Table #{{ $order->table->table_number ?? 'N/A' }}</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($order->total_price, 2) }} PLN</p>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('orders.show', $order) }}" class="flex-1 text-center py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 text-sm font-medium">
                                    {{ __('View') }}
                                </a>
                                <a href="{{ route('orders.edit', $order) }}" class="flex-1 text-center py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                                    {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 bg-white rounded-lg shadow text-gray-500">
                            {{ __('No active orders.') }}
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- 5. Today Closed --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Today Closed') }}</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    @if($todayClosed->isEmpty())
                        <p class="p-6 text-center text-gray-500">{{ __('No closed orders today.') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($todayClosed as $order)
                                <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-50">
                                    <div>
                                        <span class="font-medium text-gray-900">#{{ $order->id }}</span>
                                        <span class="text-sm text-gray-500 ml-2">Table #{{ $order->table->table_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="font-semibold text-gray-900">{{ number_format($order->total_price, 2) }} PLN</span>
                                        <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ __('View') }}</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <script>
        window.__WAITER__ = @js([
            'items' => $readyItems->map(fn($item) => [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'table_number' => $item->order->table->table_number ?? 'N/A',
                'name' => $item->menuItem->dish->name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'status' => $item->status->value,
                'unit_price' => $item->unit_price,
                'total_price' => number_format($item->quantity * $item->unit_price, 2),
                'created_at_human' => $item->created_at->diffForHumans(),
                'updated_at_human' => $item->updated_at->diffForHumans(),
                'mark_served_url' => route('waiter.mark-served', $item->id),
            ]),
            'readyStatus' => \App\Enums\OrderItemStatus::Ready->value,
            'servedStatus' => \App\Enums\OrderItemStatus::Served->value,
            'cancelledStatus' => \App\Enums\OrderItemStatus::Cancelled->value,
            'voidedStatus' => \App\Enums\OrderItemStatus::Voided->value,
            'markServedUrlTemplate' => route('waiter.mark-served', ':id'),
        ]);
    </script>
    <style>
        .animate-pulse-subtle { animation: pulseSub 2s ease-in-out infinite; }
        @keyframes pulseSub { 0%, 100% { opacity: 1; } 50% { opacity: 0.95; } }
    </style>
</x-app-layout>
