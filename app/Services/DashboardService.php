<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table;
use App\Enums\ReservationStatus;
use App\Services\Dashboard\KitchenStatsService;
use App\Services\Dashboard\RevenueStatsService;
use App\Services\Dashboard\StaffShiftService;
use App\Services\Dashboard\TopPerformersService;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const KPI_CACHE_TTL = 60;
    private const PERFORMANCE_GOOD_THRESHOLD = 10;
    private const PERFORMANCE_BELOW_THRESHOLD = -10;

    public function __construct(
        private RevenueStatsService $revenue,
        private KitchenStatsService $kitchen,
        private StaffShiftService $staff,
        private TopPerformersService $topPerformers,
    ) {}

    public function getKpis(): array
    {
        return Cache::remember('dashboard:kpis', self::KPI_CACHE_TTL, fn () => $this->calculateKpis());
    }

    private function calculateKpis(): array
    {
        $today = today();
        $todayStr = $today->toDateString();

        $revenueOrders = $this->revenue->getTodayMetrics();
        $kitchenQueue = $this->kitchen->getQueueCount();

        $tableCounts = Table::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        $activeTablesCount = (int) ($tableCounts->get('occupied') ?? 0);
        $totalTablesCount = $tableCounts->sum();

        $reservationsToday = (int) Reservation::query()
            ->whereDate('reservation_date', $todayStr)
            ->count();

        return array_merge($revenueOrders, [
            'active_tables_count' => $activeTablesCount,
            'total_tables_count' => $totalTablesCount,
            'kitchen_queue' => $kitchenQueue,
            'reservations_today' => $reservationsToday,
        ]);
    }

    public function revenueByDay(int $days = 7): array
    {
        return $this->revenue->revenueByDay($days);
    }

    /** @return array<string, float> */
    public function revenueBetween(string $from, string $to): array
    {
        return $this->revenue->revenueBetween($from, $to);
    }

    public function revenueThisMonth(): float
    {
        return $this->revenue->revenueThisMonth();
    }

    public function paymentMethodBreakdown(): array
    {
        return $this->revenue->paymentMethodBreakdown();
    }

    public function getKitchenPerformance(): array
    {
        return $this->kitchen->getPerformance();
    }

    public function staffOnShiftToday(): array
    {
        return $this->staff->staffOnShiftToday();
    }

    public function nextShiftChange(): ?string
    {
        return $this->staff->nextShiftChange();
    }

    public function getAlerts(): array
    {
        $alerts = [];
        $kpis = $this->getKpis();
        $kitchen = $this->kitchen->getPerformance();
        $staff = $this->staff->staffOnShiftToday();

        if ($kitchen['pending_over_20_min_count'] > 0) {
            $alerts[] = [
                'type' => 'orders_pending_20',
                'message' => __('Orders pending over 20 minutes'),
                'link' => route('kitchen.index'),
                'severity' => 'critical',
            ];
        }
        if ($staff['chef'] === 0) {
            $alerts[] = [
                'type' => 'no_chef_on_shift',
                'message' => __('No chef on shift today'),
                'link' => route('shifts.index'),
                'severity' => 'critical',
            ];
        }
        if ($kpis['total_tables_count'] > 0 && $kpis['active_tables_count'] >= $kpis['total_tables_count']) {
            $alerts[] = [
                'type' => 'no_tables_available',
                'message' => __('No tables available'),
                'link' => route('tables.index'),
                'severity' => 'warning',
            ];
        }
        $unconfirmed = Reservation::query()
            ->whereDate('reservation_date', today())
            ->where('status', ReservationStatus::Pending)
            ->count();
        if ($unconfirmed > 0) {
            $alerts[] = [
                'type' => 'unconfirmed_reservations',
                'message' => __(':count unconfirmed reservation(s) today', ['count' => $unconfirmed]),
                'link' => route('reservations.index'),
                'severity' => 'info',
            ];
        }
        if ($kitchen['pending_over_15_min_count'] > 0 && $kitchen['pending_over_20_min_count'] === 0) {
            $alerts[] = [
                'type' => 'orders_pending_15',
                'message' => __('Orders pending over 15 minutes'),
                'link' => route('kitchen.index'),
                'severity' => 'warning',
            ];
        }

        $order = ['critical' => 0, 'warning' => 1, 'info' => 2];
        usort($alerts, fn ($a, $b) => ($order[$a['severity']] ?? 3) <=> ($order[$b['severity']] ?? 3));
        return $alerts;
    }

    public function performanceIndicator(): string
    {
        $kpis = $this->getKpis();
        $vs = $kpis['revenue_vs_last_week'];
        if ($vs === null) {
            return 'average';
        }
        if ($vs > self::PERFORMANCE_GOOD_THRESHOLD) {
            return 'good';
        }
        if ($vs < self::PERFORMANCE_BELOW_THRESHOLD) {
            return 'below';
        }
        return 'average';
    }

    public function topDishesToday(int $limit = 5): \Illuminate\Support\Collection
    {
        return $this->topPerformers->topDishesToday($limit);
    }

    public function bestWaiterByRevenueToday(): ?\App\Models\User
    {
        return $this->topPerformers->bestWaiterByRevenueToday();
    }

    public function mostUsedPaymentMethodToday(): ?\App\Enums\PaymentMethod
    {
        return $this->topPerformers->mostUsedPaymentMethodToday();
    }
}
