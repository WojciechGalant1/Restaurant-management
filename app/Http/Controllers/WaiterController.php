<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    public function index()
    {
        $this->authorize('waiter.view');

        $user = auth()->user();

        // My Tables (waiter only; manager sees empty in this view)
        $tables = $user->role === 'waiter'
            ? Table::where('waiter_id', $user->id)->orderBy('table_number')->get()
            : collect();

        // Items ready to serve (OrderItem status = ready)
        $readyItemsQuery = OrderItem::with(['order.table', 'menuItem.dish'])
            ->where('status', 'ready')
            ->orderBy('updated_at', 'asc');

        if ($user->role === 'waiter') {
            $readyItemsQuery->whereHas('order', fn ($q) => $q->where('user_id', $user->id));
        }

        $readyItems = $readyItemsQuery->get();

        // Active orders (operational: pending, in_preparation, ready – not paid)
        $activeOrders = Order::forWaiter($user)
            ->whereIn('status', ['pending', 'in_preparation', 'ready'])
            ->with(['table', 'orderItems'])
            ->latest()
            ->get();

        // Today closed (paid today)
        $todayClosed = Order::forWaiter($user)
            ->whereDate('ordered_at', today())
            ->where('status', 'paid')
            ->with('table')
            ->latest()
            ->get();

        // Reservations for waiter's tables (for display in table cards)
        $reservationsByTable = collect();
        if ($user->role === 'waiter') {
            $tableIds = $tables->pluck('id');
            if ($tableIds->isNotEmpty()) {
                $reservations = Reservation::whereIn('table_id', $tableIds)
                    ->whereIn('status', ['pending', 'confirmed', 'seated'])
                    ->whereDate('reservation_date', '>=', today())
                    ->with('table')
                    ->orderBy('reservation_date')
                    ->orderBy('reservation_time')
                    ->get();
                
                // Group by table_id for easy lookup
                $reservationsByTable = $reservations->groupBy('table_id');
            }
        }

        return view('waiter.index', compact('readyItems', 'tables', 'activeOrders', 'todayClosed', 'reservationsByTable'));
    }

    public function markAsServed(Request $request, OrderItem $orderItem)
    {
        $this->authorize('waiter.serve-item', $orderItem);

        // Only allow marking items with 'ready' status as 'served'
        if ($orderItem->status !== 'ready') {
            return back()->with('error', 'Only ready items can be marked as served.');
        }

        $data = ['status' => 'served'];
        if (!$orderItem->ready_at) {
            $data['ready_at'] = now();
        }
        $orderItem->update($data);

        // Refresh the model to get latest data
        $orderItem->refresh();

        event(new \App\Events\OrderItemStatusUpdated($orderItem));

        return back()->with('success', 'Item marked as served.');
    }

    public function markReservationAsSeated(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === 'waiter') {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        // Only allow marking confirmed reservations as seated
        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed reservations can be marked as seated.');
        }

        $reservation->update(['status' => 'seated']);
        event(new \App\Events\ReservationUpdated($reservation));

        return back()->with('success', 'Reservation marked as seated.');
    }

    public function markReservationAsNoShow(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === 'waiter') {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        // Only allow marking confirmed reservations as no_show
        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed reservations can be marked as no show.');
        }

        $reservation->update(['status' => 'no_show']);
        event(new \App\Events\ReservationUpdated($reservation));

        return back()->with('success', 'Reservation marked as no show.');
    }

    public function updateReservationStatus(Request $request, Reservation $reservation)
    {
        $this->authorize('waiter.view');
        
        $user = auth()->user();
        
        // Verify reservation belongs to waiter's table
        if ($user->role === 'waiter') {
            $waiterTableIds = Table::where('waiter_id', $user->id)->pluck('id');
            if (!$waiterTableIds->contains($reservation->table_id)) {
                abort(403, 'This reservation does not belong to your tables.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,seated,completed,cancelled,no_show',
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $reservation->status;

        // Define allowed transitions for waiter
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['seated', 'no_show', 'cancelled'],
            'seated' => ['completed', 'cancelled'],
            'no_show' => [], // terminal state
            'completed' => [], // terminal state
            'cancelled' => [], // terminal state
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return back()->with('error', "Cannot change status from {$currentStatus} to {$newStatus}.");
        }

        $reservation->update(['status' => $newStatus]);
        event(new \App\Events\ReservationUpdated($reservation));

        $statusLabels = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'seated' => 'seated',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'no_show' => 'no show',
        ];

        return back()->with('success', "Reservation status changed to {$statusLabels[$newStatus]}.");
    }
}
