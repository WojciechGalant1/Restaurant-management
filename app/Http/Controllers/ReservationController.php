<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Reservation::class);
        $reservations = Reservation::with('table')->latest()->paginate(15);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $this->authorize('create', Reservation::class);
        $tables = Table::all();
        return view('reservations.create', compact('tables'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Reservation::class);
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'customer_name' => 'required|string',
            'phone_number' => 'required|string',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'party_size' => 'required|integer|min:1',
        ]);

        $validated['status'] = \App\Enums\ReservationStatus::Pending;

        $reservation = Reservation::create($validated);
        event(new \App\Events\ReservationCreated($reservation));
        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function edit(Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $tables = Table::all();
        return view('reservations.edit', compact('reservation', 'tables'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $validated = $request->validate([
            'table_id' => 'exists:tables,id',
            'customer_name' => 'string',
            'phone_number' => 'string',
            'reservation_date' => 'date',
            'party_size' => 'integer|min:1',
            'status' => ['sometimes', \Illuminate\Validation\Rule::enum(\App\Enums\ReservationStatus::class)],
        ]);

        $reservation->update($validated);
        event(new \App\Events\ReservationUpdated($reservation));
        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Reservation deleted successfully.');
    }
}
