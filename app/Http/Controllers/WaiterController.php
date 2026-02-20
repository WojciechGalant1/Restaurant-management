<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderItemStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaiterController extends Controller
{
    public function index()
    {
        $this->authorize('waiter.view');

        $user = auth()->user();

        // waiter only
        $tables = $user->role === UserRole::Waiter
            ? Table::where('waiter_id', $user->id)->orderBy('table_number')->get()
            : collect();

        // Items ready to serve (OrderItem status = ready)
        $readyItemsQuery = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', OrderItemStatus::Ready)
            ->orderBy('updated_at', 'asc');

        if ($user->role === UserRole::Waiter) {
            $readyItemsQuery->whereHas('order', fn ($q) => $q->where('user_id', $user->id));
        }

        $readyItems = $readyItemsQuery->get();

        // Active orders (operational: open – not paid/cancelled)
        $activeOrders = Order::forWaiter($user)
            ->where('status', OrderStatus::Open)
            ->with(['table', 'orderItems'])
            ->latest()
            ->get();

        // Today closed (paid today)
        $todayClosed = Order::forWaiter($user)
            ->whereDate('ordered_at', today())
            ->where('status', OrderStatus::Paid)
            ->with('table')
            ->latest()
            ->get();

        // Reservations for waiter's tables
        $reservationsByTable = collect();
        if ($user->role === UserRole::Waiter) {
            $tableIds = $tables->pluck('id');
            if ($tableIds->isNotEmpty()) {
                $reservations = Reservation::whereIn('table_id', $tableIds)
                    ->whereIn('status', [ReservationStatus::Pending, ReservationStatus::Confirmed, ReservationStatus::Seated])
                    ->whereDate('reservation_date', '>=', today())
                    ->with('table')
                    ->orderBy('reservation_date')
                    ->orderBy('reservation_time')
                    ->get();
                
                $reservationsByTable = $reservations->groupBy('table_id');
            }
        }

        return view('waiter.index', compact('readyItems', 'tables', 'activeOrders', 'todayClosed', 'reservationsByTable'));
    }

    public function markAsServed(Request $request, OrderItem $orderItem)
    {
        $this->authorize('waiter.serve-item', $orderItem);

        if ($orderItem->status !== OrderItemStatus::Ready) {
            return back()->with('error', 'Only ready items can be marked as served.');
        }

        $data = ['status' => OrderItemStatus::Served];
        if (!$orderItem->ready_at) {
            $data['ready_at'] = now();
        }
        $orderItem->update($data);

        $orderItem->refresh();

        event(new \App\Events\OrderItemStatusUpdated($orderItem));

        return back()->with('success', 'Item marked as served.');
    }

    public function markReservationAsSeated(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === UserRole::Waiter) {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        // Only allow marking confirmed reservations as seated
        if ($reservation->status !== ReservationStatus::Confirmed) {
            return back()->with('error', 'Only confirmed reservations can be marked as seated.');
        }

        $reservation->update(['status' => ReservationStatus::Seated]);
        event(new \App\Events\ReservationUpdated($reservation));

        return back()->with('success', 'Reservation marked as seated.');
    }

    public function markReservationAsNoShow(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === UserRole::Waiter) {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        // Only allow marking confirmed reservations as no_show
        if ($reservation->status !== ReservationStatus::Confirmed) {
            return back()->with('error', 'Only confirmed reservations can be marked as no show.');
        }

        $reservation->update(['status' => ReservationStatus::NoShow]);
        event(new \App\Events\ReservationUpdated($reservation));

        return back()->with('success', 'Reservation marked as no show.');
    }

    public function updateReservationStatus(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === UserRole::Waiter) {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ReservationStatus::class)],
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $reservation->status;

        // Define allowed transitions for waiter
        $allowedTransitions = [
            ReservationStatus::Pending->value => [ReservationStatus::Confirmed->value, ReservationStatus::Cancelled->value],
            ReservationStatus::Confirmed->value => [ReservationStatus::Seated->value, ReservationStatus::NoShow->value, ReservationStatus::Cancelled->value],
            ReservationStatus::Seated->value => [ReservationStatus::Completed->value, ReservationStatus::Cancelled->value],
            ReservationStatus::NoShow->value => [], // terminal state
            ReservationStatus::Completed->value => [], // terminal state
            ReservationStatus::Cancelled->value => [], // terminal state
        ];

        // Since we cast to enum, we need to compare enum instances or use values depending on how strict we want to be.
        // The casting on model means $currentStatus is an Enum instance (or should be).
        // The $newStatus from validation is likely the backing value (string) unless we manually cast it,
        // BUT Rule::enum ensures valid value.
        // Let's use ->value for comparison to be safe if $currentStatus is enum.
        
        $currentStatusValue = $currentStatus instanceof ReservationStatus ? $currentStatus->value : $currentStatus;
        
        if (!in_array($newStatus, $allowedTransitions[$currentStatusValue] ?? [])) {
            return back()->with('error', "Cannot change status from {$currentStatusValue} to {$newStatus}.");
        }

        $reservation->update(['status' => $newStatus]);
        event(new \App\Events\ReservationUpdated($reservation));

        return back()->with('success', "Reservation status updated.");
    }
}
