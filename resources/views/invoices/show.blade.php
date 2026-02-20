<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice') }} #{{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-bold transition">
                    {{ __('Print PDF') }}
                </button>
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 border border-gray-200 print:shadow-none print:border-none">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <x-application-logo class="w-16 h-16 fill-current text-indigo-600 mb-4" />
                        <h3 class="text-2xl font-bold">Restaurant Pro</h3>
                        <p class="text-sm text-gray-500">123 Culinary St, Food City</p>
                    </div>
                    <div class="text-right">
                        <h4 class="text-xl font-extrabold uppercase text-gray-400">Invoice</h4>
                        <p class="font-bold">#{{ $invoice->invoice_number }}</p>
                        <p class="text-sm text-gray-500">Date: {{ $invoice->issued_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>

                <div class="border-t border-b py-6 mb-8 grid grid-cols-2 gap-4">
                    <div>
                        <h5 class="text-xs font-bold uppercase text-gray-400 mb-1">Bill To:</h5>
                        <p class="font-bold">{{ $invoice->customer_name ?? 'Guest' }}</p>
                        @if($invoice->tax_id)
                            <p class="text-sm text-gray-500">Tax ID: {{ $invoice->tax_id }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <h5 class="text-xs font-bold uppercase text-gray-400 mb-1">Order Ref:</h5>
                        <p class="font-bold">Order #{{ $invoice->order_id }}</p>
                        <p class="text-sm text-gray-500">Table: #{{ $invoice->order->table->table_number ?? 'N/A' }}</p>
                    </div>
                </div>

                <table class="min-w-full mb-8">
                    <thead>
                        <tr class="border-b-2 border-gray-100">
                            <th class="py-4 text-left font-bold text-gray-900">Item</th>
                            <th class="py-4 text-center font-bold text-gray-900">Qty</th>
                            <th class="py-4 text-right font-bold text-gray-900">Price</th>
                            <th class="py-4 text-right font-bold text-gray-900">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->order->orderItems as $item)
                            <tr class="border-b border-gray-50">
                                <td class="py-4 text-gray-800">{{ $item->menuItem->dish->name ?? 'Unknown item' }}</td>
                                <td class="py-4 text-center text-gray-500">{{ $item->quantity }}</td>
                                <td class="py-4 text-right text-gray-500">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-4 text-right font-medium text-gray-900">{{ number_format($item->quantity * $item->unit_price, 2) }} PLN</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="w-64 space-y-3">
                        <div class="flex justify-between text-gray-500 uppercase text-xs font-bold tracking-widest">
                            <span>Subtotal</span>
                            <span>{{ number_format($invoice->amount, 2) }} PLN</span>
                        </div>
                        <div class="flex justify-between text-gray-500 uppercase text-xs font-bold tracking-widest border-b pb-3">
                            <span>Tax (0%)</span>
                            <span>0.00 PLN</span>
                        </div>
                        <div class="flex justify-between text-2xl font-extrabold text-indigo-600 pt-3">
                            <span>Total</span>
                            <span>{{ number_format($invoice->amount, 2) }} PLN</span>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="text-xs font-bold uppercase text-gray-400 block mb-1">Paid via:</span>
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-bold">{{ strtoupper($invoice->payment_method->value) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center text-gray-400 text-xs">
                    <p>Thank you for dining with us!</p>
                    <p>Powered by Restaurant Pro</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
