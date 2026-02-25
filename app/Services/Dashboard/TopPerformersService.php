<?php

namespace App\Services\Dashboard;

use App\Enums\PaymentMethod;
use App\Models\OrderItem;
use App\Models\Payment;
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
        $userId = Payment::query()
            ->whereDate('payments.created_at', $today)
            ->join('bills', 'payments.bill_id', '=', 'bills.id')
            ->join('orders', 'bills.order_id', '=', 'orders.id')
            ->selectRaw('orders.user_id, SUM(payments.amount) as total')
            ->groupBy('orders.user_id')
            ->orderByDesc('total')
            ->value('orders.user_id');
        return $userId ? User::find($userId) : null;
    }

    public function mostUsedPaymentMethodToday(): ?PaymentMethod
    {
        $today = today();
        $method = DB::table('payments')
            ->whereDate('created_at', $today)
            ->selectRaw('method, COUNT(*) as c')
            ->groupBy('method')
            ->orderByDesc('c')
            ->value('method');
        return $method ? PaymentMethod::from($method) : null;
    }
}
