<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Shift;
use App\Models\Table;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Events\TableStatusUpdated;
use App\Http\Requests\AssignTableRequest;
use App\Http\Requests\ReorderTablesRequest;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ReservationService;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function __construct(
        private TableService $tableService,
        private ReservationService $reservationService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Table::class);

        $user = $request->user();
        $isManager = $user->role === UserRole::Manager;
        $isHost = $user->role === UserRole::Host;

        $tables = Table::with([
                'activeAssignment.user',
                'room',
                'reservations',
            ])
            ->forWaiter($user)
            ->orderBy('sort_order')
            ->orderBy('table_number')
            ->get();

        $rooms = Room::orderBy('sort_order')->get();

        $activeShifts = $isManager
            ? $this->tableService->getActiveWaiterShifts()
            : collect();

        return view('tables.index', [
            'tables' => $tables,
            'rooms' => $rooms,
            'activeShifts' => $activeShifts,
            'currentUser' => $user,
            'isManager' => $isManager,
            'isHost' => $isHost,
        ]);
    }

    public function floorData(): JsonResponse
    {
        $this->authorize('viewAny', Table::class);

        return response()->json($this->tableService->getFloorData());
    }

    public function assign(AssignTableRequest $request, Table $table)
    {
        $validated = $request->validated();
        $shift = Shift::findOrFail($validated['shift_id']);

        if (!empty($validated['user_id'])) {
            $waiter = User::findOrFail($validated['user_id']);
            $this->tableService->assignTableToShift($table, $waiter, $shift);
        } else {
            $this->tableService->unassignTableFromShift($table, $shift);
        }

        $table->refresh();
        event(new TableStatusUpdated($table));

        return redirect()->route('tables.index')->with('success', __('Table assignment updated.'));
    }

    public function reorder(ReorderTablesRequest $request): JsonResponse
    {
        $this->tableService->reorderRoomsAndTables($request->validated());
        return response()->json(['success' => true]);
    }

    public function create()
    {
        $this->authorize('create', Table::class);
        $rooms = Room::orderBy('sort_order')->get();
        return view('tables.create', compact('rooms'));
    }

    public function store(StoreTableRequest $request)
    {
        $this->authorize('create', Table::class);

        Table::create($request->validated());
        return redirect()->route('tables.index')->with('success', 'Table created successfully.');
    }

    public function edit(Table $table)
    {
        $this->authorize('update', $table);
        $rooms = Room::orderBy('sort_order')->get();
        return view('tables.edit', compact('table', 'rooms'));
    }

    public function update(UpdateTableRequest $request, Table $table)
    {
        $this->authorize('update', $table);
        $validated = $request->validated();

        $hadStatusChange = isset($validated['status']);

        if (!empty($validated)) {
            $table->update($validated);
        }

        if ($hadStatusChange) {
            event(new TableStatusUpdated($table));
        }

        return redirect()->route('tables.index')->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table)
    {
        $this->authorize('delete', $table);
        $table->delete();
        return redirect()->route('tables.index')->with('success', 'Table deleted successfully.');
    }

    /**
     * Seat walk-in guests (Host/Manager): creates a WalkInSeated reservation and an empty order.
     */
    public function seatWalkIn(Table $table)
    {
        $this->authorize('updateStatus', $table);

        if ($table->status !== TableStatus::Available) {
            return back()->with('error', __('Only available tables can be seated as walk-in.'));
        }

        $hasOpenOrder = Order::where('table_id', $table->id)
            ->where('status', OrderStatus::Open)
            ->exists();

        if ($hasOpenOrder) {
            return back()->with('error', __('This table already has an open order.'));
        }

        try {
            $this->reservationService->seatWalkIn($table);
            $this->orderService->createEmptyOrderForTable($table);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tables.index')->with('success', __('Walk-in guests seated.'));
    }

    /**
     * Mark table as cleaned (Cleaning → Available). Waiter/Manager/Host.
     */
    public function completeCleaning(Table $table)
    {
        $this->authorize('completeCleaning', $table);

        if ($table->status !== TableStatus::Cleaning) {
            return back()->with('error', __('Only tables in cleaning status can be marked as cleaned.'));
        }

        $table->markAsAvailable();

        return back()->with('success', __('Table marked as available.'));
    }
}
