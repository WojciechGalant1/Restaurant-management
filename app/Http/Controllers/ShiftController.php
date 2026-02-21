<?php

namespace App\Http\Controllers;

use App\Enums\ShiftType;
use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\User;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Services\ShiftCreationService;
use App\Services\ShiftAnalyticsService;
use App\Services\CalendarRangeService;
use App\Services\ShiftCalendarService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        private CalendarRangeService $calendarRangeService,
        private ShiftCreationService $creationService,
        private ShiftAnalyticsService $analyticsService,
        private ShiftCalendarService $calendarService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Shift::class);

        $user = $request->user();
        $isManager = $user->role === UserRole::Manager;

        $roleFilter = null;
        if ($isManager) {
            $roleFilter = $request->input('role');
            $allowedRoles = array_map(fn (UserRole $r) => $r->value, UserRole::cases());
            if ($roleFilter !== null && $roleFilter !== '' && !in_array($roleFilter, $allowedRoles, true)) {
                $roleFilter = null;
            }
        }

        $query = Shift::with('user')
            ->orderByDesc('date')
            ->orderBy('start_time');
        if ($isManager) {
            if ($roleFilter !== null && $roleFilter !== '') {
                $query->whereHas('user', fn ($q) => $q->where('role', $roleFilter));
            }
        } else {
            $query->where('user_id', $user->id);
        }
        $shifts = $query->paginate(20)->withQueryString();

        return view('shifts.index', compact('shifts', 'roleFilter', 'isManager'));
    }

    public function create()
    {
        $this->authorize('create', Shift::class);

        $users = User::orderBy('role')->orderBy('first_name')->orderBy('last_name')->get();
        $usersByRole = [
            'kitchen'    => $users->where('role', UserRole::Chef)->values(),
            'floor'      => $users->where('role', UserRole::Waiter)->values(),
            'bar'        => $users->where('role', UserRole::Bartender)->values(),
            'management' => $users->where('role', UserRole::Manager)->values(),
        ];
        $hoursPerUser = $this->analyticsService->getHoursPerUserForCurrentWeek();

        return view('shifts.create', [
            'users'           => $users,
            'usersByRole'     => $usersByRole,
            'shiftTypes'      => ShiftType::cases(),
            'hoursPerUser'    => $hoursPerUser,
            'maxHoursPerWeek' => ShiftAnalyticsService::MAX_HOURS_PER_WEEK,
        ]);
    }

    public function store(StoreShiftRequest $request)
    {
        $this->authorize('create', Shift::class);

        $dates = $this->creationService->resolveDates(
            $request->input('date'),
            $request->input('replicate_days', [])
        );
        $userIds = $this->creationService->normalizeUserIds($request->input('user_ids', []));

        $conflict = $this->creationService->findConflict(
            $userIds,
            $dates,
            $request->input('shift_type'),
            $request->input('start_time'),
            $request->input('end_time')
        );
        if ($conflict !== null) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'user_ids' => __(":name already has this shift on :date.", [
                        'name' => $conflict['user_name'],
                        'date' => $conflict['date'],
                    ]),
                ]);
        }

        $created = $this->creationService->createMultipleShifts($userIds, $dates, [
            'shift_type' => $request->input('shift_type'),
            'start_time' => $request->input('start_time'),
            'end_time'   => $request->input('end_time'),
            'notes'      => $request->input('notes'),
        ]);

        $message = $created === 1
            ? __('Shift scheduled successfully.')
            : __(':count shifts scheduled successfully.', ['count' => $created]);
        return redirect()->route('shifts.index')->with('success', $message);
    }

    public function availability(Request $request)
    {
        $this->authorize('create', Shift::class);

        $date = $request->input('date');
        $userIds = $this->creationService->normalizeUserIds($request->input('user_ids'));

        if (!$date || empty($userIds)) {
            return response()->json([]);
        }

        $result = $this->analyticsService->getAvailabilityForUsers($date, $userIds);
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

        $result = $this->analyticsService->getCoverageForDates($dates);
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

        $user = $request->user();
        $isManager = $user->role === UserRole::Manager;

        [$viewStart, $viewEnd] = $this->calendarRangeService->fromRequest($request);
        $role = $isManager ? $request->input('role') : null;
        $userId = $isManager ? null : $user->id;

        if ($viewStart && $viewEnd) {
            $shifts = $this->calendarService->getShiftsInRange($viewStart, $viewEnd, $role, $userId);
        } else {
            $query = Shift::with('user');
            if (!$isManager) {
                $query->where('user_id', $user->id);
            }
            $shifts = $query->get();
        }

        $events = $this->calendarService->shiftsToCalendarEvents($shifts);
        return response()->json($events->values());
    }
}
