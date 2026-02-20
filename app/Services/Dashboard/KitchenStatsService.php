<?php

namespace App\Services\Dashboard;

use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Carbon;

class KitchenStatsService
{
    private const PENDING_WARNING_MINUTES = 15;
    private const PENDING_CRITICAL_MINUTES = 20;

    /** Kitchen performance metrics (avg prep time, longest waiting, status). */
    public function getPerformance(): array
    {
        $today = today();
        $now = now();
        $cutoff15 = $now->copy()->subMinutes(self::PENDING_WARNING_MINUTES);
        $cutoff20 = $now->copy()->subMinutes(self::PENDING_CRITICAL_MINUTES);

        $prepTimes = OrderItem::query()
            ->whereIn('status', [OrderItemStatus::Ready, OrderItemStatus::Served])
            ->whereDate('created_at', $today)
            ->get()
            ->map(function (OrderItem $item) {
                $end = $item->ready_at ?? $item->updated_at;
                return abs($item->created_at->diffInMinutes($end, false));
            });
        $avgPrepTimeMinutes = $prepTimes->isEmpty() ? null : round($prepTimes->avg(), 1);

        $longest = OrderItem::query()
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing])
            ->min('created_at');
        $longestWaitingOrderMinutes = $longest ? (int) Carbon::parse($longest)->diffInMinutes($now) : null;

        $pendingOver15MinCount = (int) OrderItem::query()
            ->where('status', OrderItemStatus::Pending)
            ->where('created_at', '<', $cutoff15)
            ->count();

        $pendingOver20MinCount = (int) OrderItem::query()
            ->where('status', OrderItemStatus::Pending)
            ->where('created_at', '<', $cutoff20)
            ->count();

        $status = 'ok';
        if ($pendingOver20MinCount > 0 || ($longestWaitingOrderMinutes !== null && $longestWaitingOrderMinutes >= self::PENDING_CRITICAL_MINUTES)) {
            $status = 'critical';
        } elseif ($pendingOver15MinCount > 0 || ($longestWaitingOrderMinutes !== null && $longestWaitingOrderMinutes >= self::PENDING_WARNING_MINUTES)) {
            $status = 'warning';
        }

        return [
            'avg_prep_time_minutes' => $avgPrepTimeMinutes,
            'longest_waiting_order_minutes' => $longestWaitingOrderMinutes,
            'pending_over_15_min_count' => $pendingOver15MinCount,
            'pending_over_20_min_count' => $pendingOver20MinCount,
            'status' => $status,
        ];
    }

    /** Pending/preparing counts for KPI (used by DashboardService::getKpis). */
    public function getQueueCount(): array
    {
        $kitchenQueue = OrderItem::query()
            ->whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();
            
        return [
            'pending' => (int) ($kitchenQueue[OrderItemStatus::Pending->value] ?? 0),
            'preparing' => (int) ($kitchenQueue[OrderItemStatus::Preparing->value] ?? 0),
        ];
    }
}
