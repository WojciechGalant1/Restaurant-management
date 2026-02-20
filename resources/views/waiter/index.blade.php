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

    <div class="py-6" x-data="waiterDisplay(@js($readyItems->map(fn($item) => [
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
    ])))">
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

            {{-- 2. My Tables --}}
            @if($tables->isNotEmpty())
                <section>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('My Tables') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($tables as $table)
                            @php
                                $status = $table->status instanceof \App\Enums\TableStatus ? $table->status : \App\Enums\TableStatus::tryFrom($table->status);
                                $activeOrder = $activeOrders->firstWhere('table_id', $table->id);
                                $reservation = $reservationsByTable->get($table->id)?->first();
                            @endphp
                            <div class="bg-white rounded-lg shadow border-l-4
                                {{ $status === \App\Enums\TableStatus::Occupied ? 'border-red-400' : '' }}
                                {{ $status === \App\Enums\TableStatus::Reserved ? 'border-yellow-400' : '' }}
                                {{ $status === \App\Enums\TableStatus::Available ? 'border-green-400' : '' }}">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-bold text-gray-900">Table #{{ $table->table_number }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            {{ $status === \App\Enums\TableStatus::Available ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $status === \App\Enums\TableStatus::Occupied ? 'bg-red-100 text-red-800' : '' }}
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
                                            
                                            @php
                                                $allowedStatuses = [];
                                                switch($reservation->status) {
                                                    case \App\Enums\ReservationStatus::Pending:
                                                        $allowedStatuses = [\App\Enums\ReservationStatus::Confirmed, \App\Enums\ReservationStatus::Cancelled];
                                                        break;
                                                    case \App\Enums\ReservationStatus::Confirmed:
                                                        $allowedStatuses = [\App\Enums\ReservationStatus::Seated, \App\Enums\ReservationStatus::NoShow, \App\Enums\ReservationStatus::Cancelled];
                                                        break;
                                                    case \App\Enums\ReservationStatus::Seated:
                                                        $allowedStatuses = [\App\Enums\ReservationStatus::Completed, \App\Enums\ReservationStatus::Cancelled];
                                                        break;
                                                    default:
                                                        $allowedStatuses = [];
                                                }
                                                
                                                $statusLabels = [
                                                    \App\Enums\ReservationStatus::Pending->value => __('Pending'),
                                                    \App\Enums\ReservationStatus::Confirmed->value => __('Confirmed'),
                                                    \App\Enums\ReservationStatus::Seated->value => __('Seated'),
                                                    \App\Enums\ReservationStatus::Completed->value => __('Completed'),
                                                    \App\Enums\ReservationStatus::Cancelled->value => __('Cancelled'),
                                                    \App\Enums\ReservationStatus::NoShow->value => __('No Show'),
                                                ];
                                                
                                                $statusColors = [
                                                    \App\Enums\ReservationStatus::Pending->value => 'bg-yellow-100 text-yellow-800',
                                                    \App\Enums\ReservationStatus::Confirmed->value => 'bg-blue-100 text-blue-800',
                                                    \App\Enums\ReservationStatus::Seated->value => 'bg-green-100 text-green-800',
                                                    \App\Enums\ReservationStatus::Completed->value => 'bg-gray-100 text-gray-800',
                                                    \App\Enums\ReservationStatus::Cancelled->value => 'bg-red-100 text-red-800',
                                                    \App\Enums\ReservationStatus::NoShow->value => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            
                                            @if(!empty($allowedStatuses))
                                                <form action="{{ route('waiter.reservation.update-status', $reservation) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="flex gap-1 flex-wrap">
                                                        @foreach($allowedStatuses as $status)
                                                            <button type="submit" name="status" value="{{ $status->value }}" 
                                                                    class="flex-1 py-1.5 px-2 rounded text-xs font-medium transition
                                                                    {{ $status === \App\Enums\ReservationStatus::Seated || $status === \App\Enums\ReservationStatus::Completed ? 'bg-green-600 hover:bg-green-700 text-white' : '' }}
                                                                    {{ $status === \App\Enums\ReservationStatus::NoShow || $status === \App\Enums\ReservationStatus::Cancelled ? 'bg-red-600 hover:bg-red-700 text-white' : '' }}
                                                                    {{ $status === \App\Enums\ReservationStatus::Confirmed ? 'bg-blue-600 hover:bg-blue-700 text-white' : '' }}">
                                                                {{ $statusLabels[$status->value] }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </form>
                                            @else
                                                <p class="text-xs {{ $statusColors[$reservation->status] ?? 'text-gray-600' }} mt-1 font-medium px-2 py-1 rounded">
                                                    {{ $statusLabels[$reservation->status->value] ?? ucfirst($reservation->status->value) }}
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
                                    @else
                                        <a href="{{ route('orders.create', ['table_id' => $table->id]) }}" class="block w-full text-center py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                            {{ __('Create Order') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 3. Ready to Serve (detailed) --}}
            <section id="ready-section">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Ready to Serve') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="item in items.filter(i => i.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}')" :key="item.id">
                        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-gray-900" x-text="`Table ${item.table_number}`"></span>
                                <span class="text-xs text-gray-500" x-text="item.updated_at_human"></span>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mb-1" x-text="`${item.quantity}x ${item.name}`"></p>
                            <p class="text-sm text-gray-600 mb-3" x-text="`${item.total_price} PLN`"></p>
                            <form :action="item.mark_served_url" method="POST">
                                @csrf
                                @method('PATCH')
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
        document.addEventListener('alpine:init', () => {
            if (!Alpine.store('echo')) {
                Alpine.store('echo', { connected: false });
            }

            Alpine.data('waiterDisplay', (initialItems) => ({
                items: initialItems,

                init() {
                    this.setupEcho();
                },

                setupEcho() {
                    if (window.Echo) {
                        this.connectEcho();
                    } else {
                        setTimeout(() => this.setupEcho(), 100);
                    }
                },

                connectEcho() {
                    try {
                        const channel = window.Echo.private('kitchen');
                        channel
                            .listen('.OrderItemStatusUpdated', (e) => {
                                const index = this.items.findIndex(i => i.id === e.id);
                                if (e.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}') {
                                    if (index === -1) {
                                        const markServedUrl = "{{ route('waiter.mark-served', ':id') }}".replace(':id', e.id);
                                        this.items.unshift({
                                            ...e,
                                            total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                            mark_served_url: markServedUrl
                                        });
                                    } else {
                                        this.items[index] = { ...this.items[index], ...e };
                                    }
                                } else if (e.status === '{{ \App\Enums\OrderItemStatus::Served->value }}' || e.status === '{{ \App\Enums\OrderItemStatus::Cancelled->value }}' || e.status !== '{{ \App\Enums\OrderItemStatus::Ready->value }}') {
                                    if (index !== -1) this.items.splice(index, 1);
                                }
                            })
                            .listen('.OrderItemCreated', (e) => {
                                if (e.status === '{{ \App\Enums\OrderItemStatus::Ready->value }}' && !this.items.find(i => i.id === e.id)) {
                                    const markServedUrl = "{{ route('waiter.mark-served', ':id') }}".replace(':id', e.id);
                                    this.items.unshift({
                                        ...e,
                                        total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                        mark_served_url: markServedUrl
                                    });
                                }
                            });

                        if (window.Echo.connector?.pusher) {
                            window.Echo.connector.pusher.connection.bind('connected', () => { Alpine.store('echo').connected = true; });
                            window.Echo.connector.pusher.connection.bind('disconnected', () => { Alpine.store('echo').connected = false; });
                            Alpine.store('echo').connected = true;
                        }
                    } catch (error) {
                        console.error('Echo:', error);
                        setTimeout(() => this.setupEcho(), 1000);
                    }
                }
            }));
        });
    </script>
    <style>
        .animate-pulse-subtle { animation: pulseSub 2s ease-in-out infinite; }
        @keyframes pulseSub { 0%, 100% { opacity: 1; } 50% { opacity: 0.95; } }
    </style>
</x-app-layout>
