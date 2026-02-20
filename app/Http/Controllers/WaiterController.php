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
use App\Services\ReservationService;
use App\Http\Requests\UpdateReservationStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaiterController extends Controller
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

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

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::Seated);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

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

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::NoShow);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Reservation marked as no show.');
    }

    public function updateReservationStatus(UpdateReservationStatusRequest $request, Reservation $reservation)
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

        $validated = $request->validated();

        try {
            $this->reservationService->updateStatus($reservation, $validated['status']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Reservation status updated.");
    }
}
