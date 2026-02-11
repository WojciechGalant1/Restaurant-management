<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }} #{{ $order->id }}
            </h2>
            <div class="flex space-x-3">
                @if($order->status !== 'paid')
                    <a href="{{ route('invoices.create', ['order_id' => $order->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        {{ __('Generate Invoice') }}
                    </a>
                @endif
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Items') }}</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase italic">Dish</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase italic">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase italic">Price</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase italic">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                            {{ $item->menuItem->dish->name ?? 'Unknown item' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ number_format($item->unit_price, 2) }} PLN
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                            {{ number_format($item->quantity * $item->unit_price, 2) }} PLN
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-700">Total:</td>
                                    <td class="px-4 py-3 text-xl font-extrabold text-indigo-600">
                                        {{ number_format($order->total_price, 2) }} PLN
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Meta Summary -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Summary') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs text-gray-500 block">Table</span>
                                <span class="font-bold">#{{ $order->table->table_number ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Served by</span>
                                <span class="font-bold">{{ $order->waiter->name ?? 'System' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Status</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 block">Created At</span>
                                <span class="font-medium">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
