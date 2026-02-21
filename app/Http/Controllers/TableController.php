<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Table;
use App\Models\User;
use App\Enums\UserRole;
use App\Events\TableStatusUpdated;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function __construct(
        private TableService $tableService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Table::class);

        $user = $request->user();
        $isManager = $user->role === UserRole::Manager;

        $tables = Table::with('activeAssignment.user')
            ->forWaiter($user)
            ->orderBy('table_number')
            ->get();

        $activeShifts = $isManager
            ? $this->tableService->getActiveWaiterShifts()
            : collect();

        return view('tables.index', [
            'tables' => $tables,
            'activeShifts' => $activeShifts,
            'currentUser' => $user,
            'isManager' => $isManager,
        ]);
    }

    public function floorData(): JsonResponse
    {
        $this->authorize('viewAny', Table::class);

        return response()->json($this->tableService->getFloorData());
    }

    public function assign(Request $request, Table $table)
    {
        $this->authorize('update', $table);

        $validated = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

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

    public function create()
    {
        $this->authorize('create', Table::class);
        return view('tables.create');
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
        return view('tables.edit', compact('table'));
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
}
