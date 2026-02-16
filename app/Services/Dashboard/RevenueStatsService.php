<?php

namespace App\Services\Dashboard;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RevenueStatsService
{
    private const KPI_CACHE_TTL = 60;
    private const CHARTS_CACHE_TTL = 60; // Reduced from 300s to 60s for faster updates

    public function revenueByDay(int $days = 7): array
    {
        $key = "dashboard:revenue_by_day:{$days}";
        return Cache::remember($key, self::CHARTS_CACHE_TTL, function () use ($days) {
            $start = $days === 0 ? today()->startOfDay() : now()->subDays($days)->startOfDay();
            return Invoice::query()
                ->whereBetween('issued_at', [$start, now()->endOfDay()])
                ->selectRaw('DATE(issued_at) as date, SUM(amount) as total')
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
            return Invoice::query()
                ->whereBetween('issued_at', [$start, $end])
                ->selectRaw('DATE(issued_at) as date, SUM(amount) as total')
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
            return Invoice::query()
                ->whereMonth('issued_at', now()->month)
                ->whereYear('issued_at', now()->year)
                ->sum('amount');
        });
    }

    public function paymentMethodBreakdown(): array
    {
        return Cache::remember('dashboard:payment_breakdown', self::KPI_CACHE_TTL, function () {
            $today = today();
            $totals = Invoice::query()
                ->whereDate('issued_at', $today)
                ->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method')
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

        $revenueByDate = Invoice::query()
            ->whereBetween('issued_at', [$sevenDaysStart, $todayEnd])
            ->selectRaw('DATE(issued_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn ($v) => (float) $v);

        $revenueToday = $revenueByDate->get($todayStr) ?? 0;
        $revenueYesterday = $revenueByDate->get($yesterdayStr) ?? 0;
        $revenueLast7Avg = $revenueByDate->isEmpty() ? 0 : $revenueByDate->avg();

        $revenueVsYesterday = $revenueYesterday > 0
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1)
            : null;
        $revenueVsLastWeek = $revenueLast7Avg > 0
            ? round((($revenueToday - $revenueLast7Avg) / $revenueLast7Avg) * 100, 1)
            : null;

        $paymentBreakdownToday = Invoice::query()
            ->whereDate('issued_at', $todayStr)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
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

        $avgOrderValueToday = $ordersToday > 0 ? round($revenueToday / $ordersToday, 2) : 0;

        return [
            'orders_today' => $ordersToday,
            'revenue_today' => round($revenueToday, 2),
            'avg_order_value_today' => $avgOrderValueToday,
            'revenue_vs_yesterday' => $revenueVsYesterday,
            'revenue_vs_last_week' => $revenueVsLastWeek,
            'orders_vs_yesterday' => $ordersVsYesterday,
            'payment_breakdown_today' => $paymentBreakdownToday,
        ];
    }
}
