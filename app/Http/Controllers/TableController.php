<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\User;
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
        if ($user->role === 'manager') {
            $waiters = User::where('role', 'waiter')
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

    public function store(Request $request)
    {
        $this->authorize('create', Table::class);
        $validated = $request->validate([
            'table_number' => 'required|integer|unique:tables',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        Table::create($validated);
        return redirect()->route('tables.index')->with('success', 'Table created successfully.');
    }

    public function edit(Table $table)
    {
        $this->authorize('update', $table);
        return view('tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $this->authorize('update', $table);
        $validated = $request->validate([
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:available,occupied,reserved',
            'waiter_id' => [
                'sometimes',
                'nullable',
                Rule::exists('users', 'id')->where('role', 'waiter'),
            ],
        ]);

        if (array_key_exists('waiter_id', $validated)) {
            if ($validated['waiter_id']) {
                $waiter = User::find($validated['waiter_id']);
                if ($waiter) {
                    $table->assignTo($waiter);
                }
            } else {
                // Jeśli kelner usunięty z przypisania, oznacz stolik jako dostępny
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
