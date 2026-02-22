<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Generate New Invoice')">
            <x-slot name="action">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="order_id" :value="__('Select Order (Unpaid)')" />
                            <select id="order_id" name="order_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required autofocus>
                                <option value="">-- Choose an Order --</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}" {{ (old('order_id') == $order->id || request('order_id') == $order->id) ? 'selected' : '' }}>
                                        Order #{{ $order->id }} - Table #{{ $order->table->table_number ?? 'N/A' }} ({{ number_format($order->total_price, 2) }} PLN)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('order_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_method" :value="__('Payment Method')" />
                             <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="{{ \App\Enums\PaymentMethod::Cash->value }}" {{ old('payment_method') === \App\Enums\PaymentMethod::Cash->value ? 'selected' : '' }}>Cash</option>
                                <option value="{{ \App\Enums\PaymentMethod::Card->value }}" {{ old('payment_method') === \App\Enums\PaymentMethod::Card->value ? 'selected' : '' }}>Payment Card</option>
                                <option value="{{ \App\Enums\PaymentMethod::Online->value }}" {{ old('payment_method') === \App\Enums\PaymentMethod::Online->value ? 'selected' : '' }}>Online Payment</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="customer_name" :value="__('Customer Name (Optional)')" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tax_id" :value="__('Tax ID / NIP (Optional)')" />
                            <x-text-input id="tax_id" name="tax_id" type="text" class="mt-1 block w-full" :value="old('tax_id')" />
                            <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Generate & Pay') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
