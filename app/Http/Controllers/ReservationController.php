<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Services\ReservationService;
use App\Services\ReservationCalendarService;
use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservationService,
        private ReservationCalendarService $calendarService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Reservation::class);
        $reservations = Reservation::with('table')->latest()->paginate(15);
        return view('reservations.index', compact('reservations'));
    }

    public function calendarEvents(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);

        $viewStart = $request->filled('start') ? Carbon::parse($request->input('start')) : null;
        $viewEnd = $request->filled('end') ? Carbon::parse($request->input('end')) : null;

        if (!$viewStart || !$viewEnd) {
            $reservations = Reservation::with('table')->orderBy('reservation_date')->orderBy('reservation_time')->get();
        } else {
            $reservations = $this->calendarService->getReservationsInRange($viewStart, $viewEnd);
        }

        $events = $this->calendarService->reservationsToCalendarEvents($reservations);
        return response()->json($events->values());
    }

    public function create()
    {
        $this->authorize('create', Reservation::class);
        $tables = Table::all();
        return view('reservations.create', compact('tables'));
    }

    public function store(StoreReservationRequest $request)
    {
        $this->authorize('create', Reservation::class);

        $this->reservationService->createReservation($request->validated());

        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function edit(Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $tables = Table::all();
        return view('reservations.edit', compact('reservation', 'tables'));
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $validated = $request->validated();

        if (isset($validated['status'])) {
            try {
                $this->reservationService->updateStatus($reservation, $validated['status']);
                unset($validated['status']); // Avoid mass assignment in case of direct update below
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        if (!empty($validated)) {
            $reservation->update($validated);
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Reservation deleted successfully.');
    }
}
