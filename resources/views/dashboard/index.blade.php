<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Restaurant Dashboard') }}
            </h2>
            @if($sections['kpis'] ?? false)
                @php
                    $indicator = $performanceIndicator ?? 'average';
                    $badgeClass = match($indicator) {
                        'good' => 'bg-green-100 text-green-800',
                        'below' => 'bg-red-100 text-red-800',
                        default => 'bg-yellow-100 text-yellow-800',
                    };
                    $badgeLabel = match($indicator) {
                        'good' => __('Good day'),
                        'below' => __('Below target'),
                        default => __('Average'),
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $badgeClass }}">{{ $badgeLabel }}</span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="text-gray-600">{{ __('Welcome back') }}, <span class="font-bold">{{ Auth::user()->name }}</span>!</p>

            @if($sections['kpis'] ?? false)
                {{-- KPI Cards: Revenue & Orders prominent (col-span-2), rest single --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">
                    <div class="xl:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Revenue Today') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['revenue_today'] ?? 0, 2) }} PLN</p>
                        @if(isset($kpis['revenue_vs_yesterday']) && $kpis['revenue_vs_yesterday'] !== null)
                            <p class="text-xs mt-1 {{ $kpis['revenue_vs_yesterday'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $kpis['revenue_vs_yesterday'] >= 0 ? ' +' : '' }}{{ $kpis['revenue_vs_yesterday'] }}% {{ __('vs yesterday') }}
                            </p>
                        @endif
                    </div>
                    <div class="xl:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Orders Today') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['orders_today'] ?? 0 }}</p>
                        @if(isset($kpis['orders_vs_yesterday']) && $kpis['orders_vs_yesterday'] !== null)
                            <p class="text-xs mt-1 {{ $kpis['orders_vs_yesterday'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $kpis['orders_vs_yesterday'] >= 0 ? ' +' : '' }}{{ $kpis['orders_vs_yesterday'] }}% {{ __('vs yesterday') }}
                            </p>
                        @endif
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Avg Order Value') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['avg_order_value_today'] ?? 0, 2) }} PLN</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Active Tables') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['active_tables_count'] ?? 0 }}/{{ $kpis['total_tables_count'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Kitchen Queue') }}</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ ($kpis['kitchen_queue']['pending'] ?? 0) + ($kpis['kitchen_queue']['preparing'] ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500">{{ __('pending') }}: {{ $kpis['kitchen_queue']['pending'] ?? 0 }} · {{ __('preparing') }}: {{ $kpis['kitchen_queue']['preparing'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">{{ __('Reservations Today') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['reservations_today'] ?? 0 }}</p>
                    </div>
                </div>
                @if(isset($revenueThisMonth))
                    <p class="text-sm text-gray-500 mt-2">{{ __('Total revenue this month') }}: <strong class="text-gray-900">{{ number_format($revenueThisMonth, 2) }} PLN</strong></p>
                @endif
            @endif

            @if($sections['charts'] ?? false)
                {{-- Revenue Chart + Payment Breakdown --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                            <h3 class="font-semibold text-gray-800">{{ __('Revenue') }}</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="flex rounded-lg border border-gray-200 p-0.5">
                                    <button type="button" id="revenue-chart-btn-7" class="revenue-range-btn px-3 py-1.5 text-sm font-medium rounded-md transition bg-gray-200 text-gray-700">{{ __('7 days') }}</button>
                                    <button type="button" id="revenue-chart-btn-30" class="revenue-range-btn px-3 py-1.5 text-sm font-medium rounded-md transition bg-gray-200 text-gray-700">{{ __('30 days') }}</button>
                                    <button type="button" id="revenue-chart-btn-custom" class="revenue-range-btn px-3 py-1.5 text-sm font-medium rounded-md transition bg-gray-200 text-gray-700">{{ __('Custom') }}</button>
                                </div>
                                <div id="revenue-custom-range" class="hidden flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2">
                                    <input type="date" id="revenue-custom-from" class="rounded border border-gray-300 text-sm px-2 py-1.5">
                                    <span class="text-gray-500">–</span>
                                    <input type="date" id="revenue-custom-to" class="rounded border border-gray-300 text-sm px-2 py-1.5">
                                    <button type="button" id="revenue-custom-apply" class="px-3 py-1.5 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700">{{ __('Apply') }}</button>
                                </div>
                            </div>
                        </div>
                        <div id="revenue-chart"></div>
                        @if(empty($revenueByDay7) && empty($revenueByDay30))
                            <p class="text-gray-400 text-sm py-4">{{ __('No data') }}</p>
                        @endif
                        <script>
                            window.dashboardRevenueData = {
                                data7: @json($revenueByDay7 ?? []),
                                data30: @json($revenueByDay30 ?? []),
                                customUrl: @json(route('dashboard.revenue')),
                            };
                        </script>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col">
                        <h3 class="font-semibold text-gray-800 mb-4">{{ __('Payment methods today') }}</h3>

                        <div class="flex-1 flex items-center justify-center">
                            <div id="payment-donut" class="w-full max-w-xs mx-auto"></div>
                        </div>
                        <script>
                            window.dashboardPaymentData = @json($paymentBreakdown ?? ['cash' => 0, 'card' => 0, 'online' => 0]);
                        </script>
                    </div>
                </div>
            @endif

            @if(($sections['kitchen'] ?? false) || ($sections['staff'] ?? false))
                {{-- Kitchen Performance | Staff & Shifts (one row on lg) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if($sections['kitchen'] ?? false)
                        @php
                            $kp = $kitchenPerformance ?? [];
                            $pending15 = (int)($kp['pending_over_15_min_count'] ?? 0);
                            $longest = $kp['longest_waiting_order_minutes'] ?? null;
                            $longestClass = $longest !== null && $longest >= 20 ? 'text-red-600' : ($longest !== null && $longest >= 15 ? 'text-yellow-600' : 'text-gray-900');
                            $pending15Class = $pending15 > 0 ? 'text-red-600' : 'text-gray-900';
                        @endphp
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Kitchen Performance') }}</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('Avg prep time') }}</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $kp['avg_prep_time_minutes'] !== null ? round($kp['avg_prep_time_minutes']) . ' min' : '–' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('Longest waiting') }}</p>
                                    <p class="text-xl font-bold {{ $longestClass }}">{{ $longest !== null ? $longest . ' min' : '–' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('Pending > 15 min') }}</p>
                                    <p class="text-xl font-bold {{ $pending15Class }}">{{ $pending15 }}</p>
                                </div>
                                <div>
                                    @php $status = $kp['status'] ?? 'ok'; @endphp
                                    <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $status === 'critical' ? 'bg-red-100 text-red-800' : ($status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $status === 'critical' ? __('Critical') : ($status === 'warning' ? __('Warning') : __('OK')) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($sections['staff'] ?? false)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Staff & Shifts') }}</h3>
                            <div class="flex flex-wrap gap-3">
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50 border border-gray-200">
                                    <span class="text-xl" aria-hidden="true">👨‍🍳</span>
                                    <span class="font-semibold text-gray-900">{{ $staffOnShift['chef'] ?? 0 }}</span>
                                    <span class="text-sm text-gray-600">{{ __('Chefs on shift') }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50 border border-gray-200">
                                    <span class="text-xl" aria-hidden="true">🧑‍🍽️</span>
                                    <span class="font-semibold text-gray-900">{{ $staffOnShift['waiter'] ?? 0 }}</span>
                                    <span class="text-sm text-gray-600">{{ __('Waiters on shift') }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50 border border-gray-200">
                                    <span class="text-xl" aria-hidden="true">👔</span>
                                    <span class="font-semibold text-gray-900">{{ $staffOnShift['manager'] ?? 0 }}</span>
                                    <span class="text-sm text-gray-600">{{ __('Managers on shift') }}</span>
                                </div>
                                @if($nextShiftChange ?? null)
                                    <p class="text-gray-500 text-sm self-center">{{ __('Next shift change') }}: {{ $nextShiftChange }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($sections['alerts'] ?? false)
                {{-- Alert Center (alerts already sorted critical → warning → info in DashboardService) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        {{ __('Alert Center') }}
                        @if(!empty($alerts))
                            <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ count($alerts) }}</span>
                        @endif
                    </h3>
                    @if(!empty($alerts))
                        <ul class="space-y-2">
                            @foreach($alerts as $alert)
                                <li class="flex items-center gap-3">
                                    @php
                                        $iconClass = match($alert['severity']) {
                                            'critical' => 'text-red-500',
                                            'warning' => 'text-yellow-500',
                                            default => 'text-blue-500',
                                        };
                                    @endphp
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 {{ $iconClass }}" />
                                    @if(!empty($alert['link']))
                                        <a href="{{ $alert['link'] }}" class="text-gray-700 hover:underline">{{ $alert['message'] }}</a>
                                    @else
                                        <span class="text-gray-700">{{ $alert['message'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">{{ __('No alerts') }}</p>
                    @endif
                </div>
            @endif

            @if($sections['top_performers'] ?? false)
                {{-- Top Performers --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Top Performers Today') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-2">{{ __('Top 5 dishes') }}</p>
                            <ul class="space-y-1">
                                @foreach($topDishes ?? [] as $dish)
                                    <li class="text-sm">{{ $dish['name'] }} <span class="font-medium">×{{ $dish['quantity'] }}</span></li>
                                @endforeach
                                @if(empty($topDishes))
                                    <li class="text-gray-400">{{ __('No data') }}</li>
                                @endif
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-2">{{ __('Best waiter (revenue)') }}</p>
                            <p class="font-medium">{{ $bestWaiter ? $bestWaiter->name : __('No data') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-2">{{ __('Most used payment') }}</p>
                            <p class="font-medium capitalize">{{ $mostUsedPaymentMethod ?? __('No data') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($sections['live_feed'] ?? false)
                {{-- Live Activity Feed: relative time, hover, fade-in --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="dashboardLiveFeed()">
                    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Live Activity') }}</h3>
                    <ul class="space-y-0 max-h-80 overflow-y-auto" id="dashboard-feed-list">
                        <template x-for="(item, index) in feedItems" :key="item.id">
                            <li class="flex items-start gap-3 text-sm py-2.5 px-2 -mx-2 rounded-md border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100">
                                <span class="text-gray-400 text-xs shrink-0 w-16" x-text="relativeTime(item.added_at)"></span>
                                <template x-if="item.feed_link">
                                    <a :href="item.feed_link" class="text-gray-700 hover:text-indigo-600 hover:underline" x-text="item.feed_message"></a>
                                </template>
                                <template x-if="!item.feed_link">
                                    <span class="text-gray-700" x-text="item.feed_message"></span>
                                </template>
                            </li>
                        </template>
                        <li x-show="feedItems.length === 0" class="text-gray-500 text-sm py-4">{{ __('No activity yet.') }}</li>
                    </ul>
                </div>
                <script>
                    document.addEventListener('alpine:init', () => {
                        if (!Alpine.store('echo')) Alpine.store('echo', { connected: false });
                        Alpine.data('dashboardLiveFeed', () => ({
                            feedItems: [],
                            maxItems: 50,
                            feedId: 0,
                            relativeTime(ts) {
                                if (!ts) return '–';
                                const sec = Math.floor((Date.now() - ts) / 1000);
                                if (sec < 60) return 'just now';
                                if (sec < 3600) return Math.floor(sec / 60) + ' min ago';
                                if (sec < 86400) return Math.floor(sec / 3600) + ' h ago';
                                return Math.floor(sec / 86400) + ' d ago';
                            },
                            init() {
                                if (typeof window.Echo === 'undefined') {
                                    setTimeout(() => this.init(), 200);
                                    return;
                                }
                                setInterval(() => {
                                    this.feedItems = this.feedItems.slice();
                                }, 60000);
                                try {
                                    const channel = window.Echo.private('dashboard');
                                    const self = this;
                                    const push = (e) => {
                                        const msg = e.feed_message || e.message;
                                        const link = e.feed_link || e.link || null;
                                        if (msg) {
                                            self.feedItems.unshift({
                                                id: ++self.feedId,
                                                feed_message: msg,
                                                feed_link: link,
                                                added_at: Date.now()
                                            });
                                            if (self.feedItems.length > self.maxItems) self.feedItems = self.feedItems.slice(0, self.maxItems);
                                        }
                                    };
                                    channel.listen('.OrderCreated', push)
                                        .listen('.OrderItemStatusUpdated', push)
                                        .listen('.ReservationCreated', push)
                                        .listen('.ReservationUpdated', push)
                                        .listen('.InvoiceIssued', push);
                                    if (window.Echo.connector && window.Echo.connector.pusher) {
                                        window.Echo.connector.pusher.connection.bind('connected', () => { Alpine.store('echo').connected = true; });
                                        Alpine.store('echo').connected = true;
                                    }
                                } catch (err) { console.error('Dashboard Live Feed:', err); }
                            }
                        }));
                    });
                </script>
            @endif

            @if($sections['quick_actions'] ?? true)
                {{-- Quick Actions: 3–4 accent colors, rest neutral (indigo, amber, slate, gray) --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Quick Actions') }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4">
                        <a href="{{ route('orders.create') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-indigo-500">
                            <x-heroicon-o-shopping-bag class="w-8 h-8 text-indigo-500" />
                            <span class="font-medium text-gray-900">{{ __('New Order') }}</span>
                        </a>
                        <a href="{{ route('reservations.create') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-amber-500">
                            <x-heroicon-o-calendar-days class="w-8 h-8 text-amber-500" />
                            <span class="font-medium text-gray-900">{{ __('New Reservation') }}</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-slate-400">
                            <x-heroicon-o-users class="w-8 h-8 text-slate-500" />
                            <span class="font-medium text-gray-900">{{ __('Staff') }}</span>
                        </a>
                        <a href="{{ route('kitchen.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-slate-400">
                            <x-heroicon-o-fire class="w-8 h-8 text-slate-500" />
                            <span class="font-medium text-gray-900">{{ __('Kitchen') }}</span>
                        </a>
                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-gray-300">
                            <x-heroicon-o-document-text class="w-8 h-8 text-gray-500" />
                            <span class="font-medium text-gray-900">{{ __('Orders') }}</span>
                        </a>
                        @can('viewAny', App\Models\Table::class)
                            <a href="{{ route('tables.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-gray-300">
                                <x-heroicon-o-squares-2x2 class="w-8 h-8 text-gray-500" />
                                <span class="font-medium text-gray-900">{{ __('Tables') }}</span>
                            </a>
                        @endcan
                        <a href="{{ route('menu-items.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-gray-300">
                            <x-heroicon-o-book-open class="w-8 h-8 text-gray-500" />
                            <span class="font-medium text-gray-900">{{ __('Menu') }}</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition border-l-4 border-gray-300">
                            <x-heroicon-o-credit-card class="w-8 h-8 text-gray-500" />
                            <span class="font-medium text-gray-900">{{ __('Invoices') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($sections['charts'] ?? false)
        @vite(['resources/js/dashboard-chart.js'])
    @endif
</x-app-layout>
