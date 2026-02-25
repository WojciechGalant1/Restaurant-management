<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }} #{{ $order->id }}
            </h2>
            <div class="flex flex-wrap gap-3 items-center">
                @can('update', $order)
                    @if(!$order->openBill())
                        <a href="{{ route('orders.edit', $order) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                            {{ __('Edit Order') }}
                        </a>
                    @endif
                @endcan
                @php $openBill = $order->openBill(); $paidBill = $order->paidBill(); @endphp
                @if(!$openBill && !$paidBill && $order->orderItems->isNotEmpty())
                    @can('create', \App\Models\Bill::class)
                        <form action="{{ route('orders.bill.store', $order) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                {{ __('Create Bill') }}
                            </button>
                        </form>
                    @endcan
                @endif
                @if($openBill)
                    @can('addPayment', $openBill)
                        <button type="button" onclick="document.getElementById('add-payment-modal').showModal()" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">
                            {{ __('Add Payment') }}
                        </button>
                        <form action="{{ route('bills.cancel', $openBill) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Cancel this bill? Order will be editable again.') }}');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 transition">
                                {{ __('Cancel Bill') }}
                            </button>
                        </form>
                    @endcan
                @endif
                @if($paidBill)
                    @if($paidBill->invoice)
                        <a href="{{ route('invoices.show', $paidBill->invoice) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            {{ __('View Invoice') }}
                        </a>
                    @else
                        <a href="{{ route('invoices.create', ['bill_id' => $paidBill->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            {{ __('Generate Invoice') }}
                        </a>
                    @endif
                @endif
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <x-flash-message type="success" />
        <x-flash-message type="error" />
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

                    @if($openBill)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Bill') }} #{{ $openBill->id }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('Total') }}</span>
                                    <span class="font-bold">{{ number_format($openBill->total_amount, 2) }} PLN</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('Paid') }}</span>
                                    <span class="font-bold">{{ number_format($openBill->totalPaid(), 2) }} PLN</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('Remaining') }}</span>
                                    <span class="font-bold">{{ number_format(max(0, (float)$openBill->total_amount - $openBill->totalPaid()), 2) }} PLN</span>
                                </div>
                                @if($openBill->tip_amount > 0)
                                    <div>
                                        <span class="text-xs text-gray-500 block">{{ __('Tip') }}</span>
                                        <span class="font-bold">{{ number_format($openBill->tip_amount, 2) }} PLN</span>
                                    </div>
                                @endif
                            </div>
                            @if($openBill->payments->isNotEmpty())
                                <div class="mb-4">
                                    <span class="text-xs text-gray-500 block">{{ __('Payments') }}</span>
                                    <ul class="text-sm space-y-1">
                                        @foreach($openBill->payments as $p)
                                            <li>{{ number_format($p->amount, 2) }} PLN ({{ $p->method->label() }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
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
                                        {{ $order->status === \App\Enums\OrderStatus::Open ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $order->status === \App\Enums\OrderStatus::Paid ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === \App\Enums\OrderStatus::Cancelled ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $order->status->label() }}
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

    @if($openBill)
        <dialog id="add-payment-modal" class="rounded-lg shadow-xl p-6 w-full max-w-md backdrop:bg-black/50" @if($errors->has('amount') || $errors->has('method')) open @endif>
            <h3 class="text-lg font-semibold mb-4">{{ __('Add Payment') }}</h3>
            <form action="{{ route('bills.payments.store', $openBill) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('Amount') }} (PLN)</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount', max(0.01, (float)$openBill->total_amount - $openBill->totalPaid())) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="method" class="block text-sm font-medium text-gray-700">{{ __('Payment Method') }}</label>
                        <select name="method" id="method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(\App\Enums\PaymentMethod::cases() as $m)
                                <option value="{{ $m->value }}" @selected(old('method', 'card') === $m->value)>{{ $m->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('add-payment-modal').close()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                        {{ __('Add Payment') }}
                    </button>
                </div>
            </form>
        </dialog>
    @endif
</x-app-layout>
