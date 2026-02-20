<?php

namespace App\Http\Controllers;

use App\Enums\ShiftType;
use App\Models\Shift;
use App\Models\User;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Shift::class);
        $shifts = Shift::with('user')
            ->orderByDesc('date')
            ->orderBy('start_time')
            ->paginate(20);
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        $this->authorize('create', Shift::class);
        $users = User::all();
        $shiftTypes = ShiftType::cases();
        return view('shifts.create', compact('users', 'shiftTypes'));
    }

    public function store(StoreShiftRequest $request)
    {
        $this->authorize('create', Shift::class);

        Shift::create($request->validated());
        return redirect()->route('shifts.index')->with('success', 'Shift scheduled successfully.');
    }

    public function edit(Shift $shift)
    {
        $this->authorize('update', $shift);
        $users = User::all();
        $shiftTypes = ShiftType::cases();
        return view('shifts.edit', compact('shift', 'users', 'shiftTypes'));
    }

    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        $this->authorize('update', $shift);
        $shift->update($request->validated());
        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        $this->authorize('delete', $shift);
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
    }

    public function calendarEvents(Request $request)
    {
        $this->authorize('viewAny', Shift::class);

        $query = Shift::with('user');

        if ($request->filled('start')) {
            $query->where('date', '>=', $request->input('start'));
        }
        if ($request->filled('end')) {
            $query->where('date', '<=', $request->input('end'));
        }

        $shifts = $query->get();

        $colors = [
            'morning'  => ['bg' => '#f59e0b', 'border' => '#d97706', 'text' => '#78350f'],
            'evening'  => ['bg' => '#6366f1', 'border' => '#4f46e5', 'text' => '#ffffff'],
            'full_day' => ['bg' => '#10b981', 'border' => '#059669', 'text' => '#ffffff'],
        ];

        $events = $shifts->map(function (Shift $shift) use ($colors) {
            $date = $shift->date->format('Y-m-d');
            $c = $colors[$shift->shift_type->value] ?? $colors['morning'];
            $userName = trim(($shift->user->first_name ?? '') . ' ' . ($shift->user->last_name ?? '')) ?: 'Unknown';
            $role = ucfirst($shift->user->role->value ?? '');
            $start = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
            $end = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);

            if ($end <= $start) {
                $end = $end->addDay();
            }

            return [
                'id'              => $shift->id,
                'title'           => "{$userName} ({$role})",
                'start'           => $start->toIso8601String(),
                'end'             => $end->toIso8601String(),
                'backgroundColor' => $c['bg'],
                'borderColor'     => $c['border'],
                'textColor'       => $c['text'],
                'extendedProps'   => [
                    'shiftType' => $shift->shift_type->label(),
                    'userName'  => $userName,
                    'role'      => $role,
                    'notes'     => $shift->notes,
                    'editUrl'   => route('shifts.edit', $shift),
                ],
            ];
        });

        return response()->json($events->values());
    }
}
