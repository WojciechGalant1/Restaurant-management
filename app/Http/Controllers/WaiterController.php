<?php

namespace App\Http\Controllers;

use App\Enums\OrderItemStatus;
use App\Enums\ReservationStatus;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Services\WaiterDashboardService;
use App\Http\Requests\UpdateReservationStatusRequest;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    public function __construct(
        private ReservationService $reservationService,
        private WaiterDashboardService $waiterDashboard
    ) {}

    public function index()
    {
        $this->authorize('waiter.view');

        $data = $this->waiterDashboard->getDashboardData(auth()->user());
        return view('waiter.index', $data);
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
        $this->authorize('updateAsWaiter', $reservation);

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::Seated);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Reservation marked as seated.');
    }

    public function markReservationAsNoShow(Request $request, Reservation $reservation)
    {
        $this->authorize('updateAsWaiter', $reservation);

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::NoShow);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Reservation marked as no show.');
    }

    public function updateReservationStatus(UpdateReservationStatusRequest $request, Reservation $reservation)
    {
        $this->authorize('updateAsWaiter', $reservation);

        $validated = $request->validated();

        try {
            $this->reservationService->updateStatus($reservation, $validated['status']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Reservation status updated.");
    }
}
