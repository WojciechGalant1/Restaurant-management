<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Table;
use App\Models\User;
use App\Enums\UserRole;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Table::class);

        $user = $request->user();

        $tables = Table::with('waiter')
            ->forWaiter($user)
            ->orderBy('table_number')
            ->get();

        $waiters = [];
        if ($user->role === UserRole::Manager) {
            $activeWaiterIds = Shift::activeNow()->pluck('user_id');
            $waiters = User::where('role', UserRole::Waiter)
                ->whereIn('id', $activeWaiterIds)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }

        return view('tables.index', [
            'tables' => $tables,
            'waiters' => $waiters,
            'currentUser' => $user,
        ]);
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

        if (array_key_exists('waiter_id', $validated)) {
            if ($validated['waiter_id']) {
                $waiter = User::find($validated['waiter_id']);
                if ($waiter) {
                    $table->assignTo($waiter);
                }
            } else {
                $table->markAsAvailable();
            }
            unset($validated['waiter_id']);
        }

        if (!empty($validated)) {
            $table->update($validated);
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
