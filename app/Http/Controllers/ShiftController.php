<?php

namespace App\Http\Controllers;

use App\Enums\ShiftType;
use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\User;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    private const MAX_HOURS_PER_WEEK = 40;
    private const MAX_HOURS_PER_DAY = 12;

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

        $users = User::orderBy('role')->orderBy('first_name')->orderBy('last_name')->get();
        $shiftTypes = ShiftType::cases();

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $shiftsThisWeek = Shift::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();
        $hoursPerUser = $shiftsThisWeek->groupBy('user_id')->map(fn ($shifts) => round($shifts->sum(fn ($s) => $s->durationInHours()), 1));

        $usersByRole = [
            'kitchen' => $users->where('role', UserRole::Chef)->values(),
            'floor'   => $users->where('role', UserRole::Waiter)->values(),
            'bar'     => $users->where('role', UserRole::Bartender)->values(),
            'management' => $users->where('role', UserRole::Manager)->values(),
        ];

        return view('shifts.create', [
            'users' => $users,
            'usersByRole' => $usersByRole,
            'shiftTypes' => $shiftTypes,
            'hoursPerUser' => $hoursPerUser,
            'maxHoursPerWeek' => self::MAX_HOURS_PER_WEEK,
        ]);
    }

    public function store(StoreShiftRequest $request)
    {
        $this->authorize('create', Shift::class);

        $dates = $this->resolveDates($request->input('date'), $request->input('replicate_days', []));
        $userIds = $request->input('user_ids', []);
        if (is_string($userIds)) {
            $userIds = array_filter(explode(',', $userIds));
        }
        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        foreach ($userIds as $userId) {
            foreach ($dates as $dateStr) {
                $exists = Shift::where('user_id', $userId)
                    ->where('date', $dateStr)
                    ->where('shift_type', $request->input('shift_type'))
                    ->exists();
                if ($exists) {
                    $user = User::find($userId);
                    $name = $user ? trim($user->first_name . ' ' . $user->last_name) : $userId;
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['user_ids' => __(":name already has this shift on :date.", ['name' => $name, 'date' => $dateStr])]);
                }
            }
        }

        $created = 0;
        DB::transaction(function () use ($request, $dates, $userIds, &$created) {
            foreach ($userIds as $userId) {
                foreach ($dates as $dateStr) {
                    Shift::create([
                        'user_id' => $userId,
                        'date' => $dateStr,
                        'shift_type' => $request->input('shift_type'),
                        'start_time' => $request->input('start_time'),
                        'end_time' => $request->input('end_time'),
                        'notes' => $request->input('notes'),
                    ]);
                    $created++;
                }
            }
        });

        $message = $created === 1
            ? __('Shift scheduled successfully.')
            : __(':count shifts scheduled successfully.', ['count' => $created]);
        return redirect()->route('shifts.index')->with('success', $message);
    }

    /**
     * Resolve list of dates: either [date] or weekdays in the week of date.
     */
    private function resolveDates(string $baseDate, array $replicateDays): array
    {
        $base = Carbon::parse($baseDate);
        if (empty($replicateDays)) {
            return [$base->toDateString()];
        }
        $weekStart = $base->copy()->startOfWeek(Carbon::MONDAY);
        $dates = [];
        foreach ($replicateDays as $d) {
            $d = (int) $d;
            if ($d >= 1 && $d <= 7) {
                $day = $weekStart->copy()->addDays($d - 1);
                $dates[] = $day->toDateString();
            }
        }
        $dates = array_unique($dates);
        sort($dates);
        return $dates;
    }

    public function availability(Request $request)
    {
        $this->authorize('create', Shift::class);

        $date = $request->input('date');
        $userIds = $request->input('user_ids');
        if (is_string($userIds)) {
            $userIds = array_filter(array_map('intval', explode(',', $userIds)));
        }
        $userIds = $userIds ?: [];

        if (!$date || empty($userIds)) {
            return response()->json([]);
        }

        $base = Carbon::parse($date);
        $weekStart = $base->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $shiftsOnDate = Shift::with('user')
            ->whereIn('user_id', $userIds)
            ->where('date', $date)
            ->get()
            ->groupBy('user_id');

        $shiftsThisWeek = Shift::whereIn('user_id', $userIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('user_id');

        $result = [];
        foreach ($userIds as $uid) {
            $onDate = $shiftsOnDate->get($uid, collect());
            $weekShifts = $shiftsThisWeek->get($uid, collect());
            $hoursToday = round($onDate->sum(fn ($s) => $s->durationInHours()), 1);
            $hoursWeek = round($weekShifts->sum(fn ($s) => $s->durationInHours()), 1);
            $result[$uid] = [
                'shifts_today' => $onDate->map(fn ($s) => [
                    'id' => $s->id,
                    'shift_type' => $s->shift_type->label(),
                    'start_time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                ])->values()->all(),
                'hours_today' => $hoursToday,
                'hours_week' => $hoursWeek,
                'conflict' => $onDate->isNotEmpty(),
                'exceeds_day' => $hoursToday >= self::MAX_HOURS_PER_DAY,
                'exceeds_week' => $hoursWeek >= self::MAX_HOURS_PER_WEEK,
            ];
        }
        return response()->json($result);
    }

    public function coverage(Request $request)
    {
        $this->authorize('create', Shift::class);

        $dates = $request->input('dates', $request->input('date'));
        if (is_string($dates)) {
            $dates = array_filter(explode(',', $dates));
        }
        $dates = array_values(array_unique($dates));
        if (empty($dates)) {
            return response()->json([]);
        }

        $shifts = Shift::with('user')
            ->whereIn('date', $dates)
            ->get()
            ->groupBy('date');

        $result = [];
        foreach ($dates as $d) {
            $dayShifts = $shifts->get($d, collect());
            $result[$d] = [
                'chef' => $dayShifts->where('user.role', UserRole::Chef)->count(),
                'waiter' => $dayShifts->where('user.role', UserRole::Waiter)->count(),
                'bartender' => $dayShifts->where('user.role', UserRole::Bartender)->count(),
                'manager' => $dayShifts->where('user.role', UserRole::Manager)->count(),
            ];
        }
        return response()->json($result);
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

        if ($request->filled('start') && $request->filled('end')) {
            $viewStart = Carbon::parse($request->input('start'));
            $viewEnd = Carbon::parse($request->input('end'));

            $query->where(function ($q) use ($viewStart, $viewEnd) {
                $q->whereRaw(
                    'TIMESTAMP(date, start_time) < ?',
                    [$viewEnd]
                )->whereRaw(
                    'IF(end_time > start_time, TIMESTAMP(date, end_time), TIMESTAMP(DATE_ADD(date, INTERVAL 1 DAY), end_time)) > ?',
                    [$viewStart]
                );
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('user', fn ($q) => $q->where('role', $request->input('role')));
        }

        $shifts = $query->get();

        $events = $shifts->map(function (Shift $shift) {
            $date = $shift->date->format('Y-m-d');
            $c = $shift->shift_type->color();
            $userName = trim(($shift->user->first_name ?? '') . ' ' . ($shift->user->last_name ?? '')) ?: 'Unknown';
            $role = ucfirst($shift->user->role->value ?? '');
            $start = Carbon::parse($date . ' ' . $shift->start_time);
            $end = Carbon::parse($date . ' ' . $shift->end_time);

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
