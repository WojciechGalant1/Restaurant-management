<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Services\CalendarRangeService;
use App\Services\ReservationService;
use App\Services\ReservationCalendarService;
use App\Services\TableAvailabilityService;
use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function __construct(
        private CalendarRangeService $calendarRangeService,
        private ReservationService $reservationService,
        private ReservationCalendarService $calendarService,
        private TableAvailabilityService $tableAvailabilityService
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

        [$viewStart, $viewEnd] = $this->calendarRangeService->fromRequest($request);

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
        return view('reservations.create');
    }

    public function availableTables(Request $request)
    {
        $this->authorize('create', Reservation::class);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'party_size' => 'required|integer|min:1',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        $dateTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $duration = $validated['duration'] ?? 120;

        $result = $this->tableAvailabilityService->forSlot(
            $dateTime,
            (int) $validated['party_size'],
            (int) $duration
        );

        return response()->json($result);
    }

    public function customerByPhone(Request $request)
    {
        $this->authorize('create', Reservation::class);

        $phone = $request->query('phone', '');
        if (strlen(trim($phone)) < 3) {
            return response()->json([]);
        }

        $reservation = Reservation::where('phone_number', 'like', '%' . trim($phone) . '%')
            ->where('phone_number', '!=', 'walk-in')
            ->latest()
            ->first();

        if (!$reservation) {
            return response()->json([]);
        }

        return response()->json(['customer_name' => $reservation->customer_name]);
    }

    public function store(StoreReservationRequest $request)
    {
        $this->authorize('create', Reservation::class);

        $validated = $request->validated();
        $dateTime = Carbon::parse($validated['reservation_date'] . ' ' . $validated['reservation_time']);

        if (!$this->tableAvailabilityService->isTableAvailableForSlot(
            (int) $validated['table_id'],
            $dateTime,
            (int) $validated['duration_minutes']
        )) {
            return back()
                ->withInput()
                ->with('error', __('This table is no longer available for the selected slot. Please choose another table.'));
        }

        $this->reservationService->createReservation($validated);

        return redirect()->route('reservations.index')->with('success', __('Reservation created successfully.'));
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
        if (!empty($validated)) {
            $reservation->update($validated);
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    public function confirm(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::Confirmed);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Reservation confirmed.'));
    }

    public function seat(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::Seated);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Guests seated.'));
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        try {
            $this->reservationService->updateStatus($reservation, ReservationStatus::Cancelled);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Reservation cancelled.'));
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Reservation deleted successfully.');
    }
}
