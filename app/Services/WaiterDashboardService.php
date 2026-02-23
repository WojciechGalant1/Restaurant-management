<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Collection;

class WaiterDashboardService
{
    /**
     * Data for the waiter dashboard (index view).
     */
    public function getDashboardData(User $user): array
    {
        $tables = $user->role === UserRole::Waiter
            ? Table::forWaiter($user)->orderBy('table_number')->get()
            : collect();

        $tableIds = $tables->pluck('id');

        $readyItemsQuery = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', OrderItemStatus::Ready)
            ->orderBy('updated_at', 'asc');

        if ($user->role === UserRole::Waiter) {
            $readyItemsQuery->whereHas('order', fn ($q) => $q->whereIn('table_id', $tableIds));
        }

        $readyItems = $readyItemsQuery->get();

        $activeOrders = $user->role === UserRole::Waiter
            ? Order::where('status', OrderStatus::Open)
                ->whereIn('table_id', $tableIds)
                ->with(['table', 'orderItems'])
                ->latest()
                ->get()
            : collect();

        $todayClosed = $user->role === UserRole::Waiter
            ? Order::whereDate('ordered_at', today())
                ->where('status', OrderStatus::Paid)
                ->whereIn('table_id', $tableIds)
                ->with('table')
                ->latest()
                ->get()
            : collect();

        $reservationsByTable = $this->getReservationsByTableForUser($user, $tables);

        return [
            'readyItems'        => $readyItems,
            'tables'            => $tables,
            'activeOrders'      => $activeOrders,
            'todayClosed'       => $todayClosed,
            'reservationsByTable' => $reservationsByTable,
        ];
    }

    private function getReservationsByTableForUser(User $user, Collection $tables): Collection
    {
        if ($user->role !== UserRole::Waiter || $tables->isEmpty()) {
            return collect();
        }

        $tableIds = $tables->pluck('id');
        $reservations = Reservation::whereIn('table_id', $tableIds)
            ->whereIn('status', [ReservationStatus::Pending, ReservationStatus::Confirmed, ReservationStatus::WalkInSeated, ReservationStatus::Seated])
            ->whereDate('reservation_date', '>=', today())
            ->with('table')
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return $reservations->groupBy('table_id');
    }
}
