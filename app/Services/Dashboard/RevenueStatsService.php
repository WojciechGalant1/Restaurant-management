<?php

namespace App\Services\Dashboard;

use App\Models\Bill;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RevenueStatsService
{
    private const KPI_CACHE_TTL = 60;
    private const CHARTS_CACHE_TTL = 60;

    public function revenueByDay(int $days = 7): array
    {
        $key = "dashboard:revenue_by_day:{$days}";
        return Cache::remember($key, self::CHARTS_CACHE_TTL, function () use ($days) {
            $start = $days === 0 ? today()->startOfDay() : now()->subDays($days)->startOfDay();
            return Payment::query()
                ->whereBetween('created_at', [$start, now()->endOfDay()])
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->map(fn ($v) => (float) $v)
                ->all();
        });
    }

    /** @return array<string, float> */
    public function revenueBetween(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        if ($start->gt($end)) {
            return [];
        }
        $key = 'dashboard:revenue_between:' . $start->format('Y-m-d') . ':' . $end->format('Y-m-d');
        return Cache::remember($key, 60, function () use ($start, $end) {
            return Payment::query()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->map(fn ($v) => (float) $v)
                ->all();
        });
    }

    public function revenueThisMonth(): float
    {
        $key = 'dashboard:revenue_this_month:' . now()->format('Y-m');
        return (float) Cache::remember($key, self::KPI_CACHE_TTL, function () {
            return Payment::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');
        });
    }

    public function paymentMethodBreakdown(): array
    {
        return Cache::remember('dashboard:payment_breakdown', self::KPI_CACHE_TTL, function () {
            $today = today();
            $totals = Payment::query()
                ->whereDate('created_at', $today)
                ->selectRaw('method, SUM(amount) as total')
                ->groupBy('method')
                ->pluck('total', 'method')
                ->map(fn ($v) => (float) $v)
                ->all();
            return array_merge(['cash' => 0, 'card' => 0, 'online' => 0], $totals);
        });
    }

    /** Revenue + orders subset for KPI (used by DashboardService::getKpis). */
    public function getTodayMetrics(): array
    {
        $today = today();
        $todayStr = $today->toDateString();
        $yesterdayStr = $today->copy()->subDay()->toDateString();
        $sevenDaysStart = $today->copy()->subDays(7)->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $revenueByDate = Payment::query()
            ->whereBetween('created_at', [$sevenDaysStart, $todayEnd])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn ($v) => (float) $v);

        $revenueToday = $revenueByDate->get($todayStr) ?? 0;
        $revenueYesterday = $revenueByDate->get($yesterdayStr) ?? 0;
        $revenueLast7Avg = $revenueByDate->isEmpty() ? 0 : $revenueByDate->avg();

        $revenueVsYesterday = match (true) {
            $revenueYesterday > 0 => round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1),
            $revenueYesterday == 0 && $revenueToday > 0 => 100.0,
            default => 0.0,
        };
        $revenueVsLastWeek = $revenueLast7Avg > 0
            ? round((($revenueToday - $revenueLast7Avg) / $revenueLast7Avg) * 100, 1)
            : null;

        $paymentBreakdownToday = Payment::query()
            ->whereDate('created_at', $todayStr)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->map(fn ($v) => (float) $v)
            ->all();

        $ordersByDate = Order::query()
            ->whereBetween('ordered_at', [$sevenDaysStart, $todayEnd])
            ->selectRaw('DATE(ordered_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $ordersToday = (int) ($ordersByDate->get($todayStr) ?? 0);
        $ordersYesterday = (int) ($ordersByDate->get($yesterdayStr) ?? 0);
        $ordersVsYesterday = $ordersYesterday > 0
            ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100, 1)
            : null;

        // Avg order value: only paid orders today (consistent with revenue from payments)
        $paidOrdersToday = Bill::query()
            ->whereDate('paid_at', $todayStr)
            ->count();
        $avgOrderValueToday = $paidOrdersToday > 0 ? round($revenueToday / $paidOrdersToday, 2) : 0;

        $tipsToday = (float) \App\Models\Bill::query()
            ->whereDate('paid_at', $todayStr)
            ->sum('tip_amount');

        return [
            'orders_today' => $ordersToday,
            'revenue_today' => round($revenueToday, 2),
            'tips_today' => round($tipsToday, 2),
            'avg_order_value_today' => $avgOrderValueToday,
            'revenue_vs_yesterday' => $revenueVsYesterday,
            'revenue_vs_last_week' => $revenueVsLastWeek,
            'orders_vs_yesterday' => $ordersVsYesterday,
            'payment_breakdown_today' => $paymentBreakdownToday,
        ];
    }
}
