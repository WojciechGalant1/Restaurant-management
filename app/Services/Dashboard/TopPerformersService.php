<?php

namespace App\Services\Dashboard;

use App\Models\Invoice;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TopPerformersService
{
    public function topDishesToday(int $limit = 5): \Illuminate\Support\Collection
    {
        $today = today();
        $ids = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereDate('ordered_at', $today))
            ->select('menu_item_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
        $menuItems = \App\Models\MenuItem::with('dish')->whereIn('id', $ids->pluck('menu_item_id'))->get()->keyBy('id');
        return $ids->map(function ($row) use ($menuItems) {
            $dish = $menuItems->get($row->menu_item_id)?->dish;
            return [
                'name' => $dish?->name ?? __('Unknown'),
                'quantity' => (int) $row->total_quantity,
            ];
        });
    }

    public function bestWaiterByRevenueToday(): ?User
    {
        $today = today();
        $userId = Invoice::query()
            ->whereDate('issued_at', $today)
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->selectRaw('orders.user_id, SUM(invoices.amount) as total')
            ->groupBy('orders.user_id')
            ->orderByDesc('total')
            ->value('orders.user_id');
        return $userId ? User::find($userId) : null;
    }

    public function mostUsedPaymentMethodToday(): ?string
    {
        $today = today();
        return Invoice::query()
            ->whereDate('issued_at', $today)
            ->selectRaw('payment_method, COUNT(*) as c')
            ->groupBy('payment_method')
            ->orderByDesc('c')
            ->value('payment_method');
    }
}
