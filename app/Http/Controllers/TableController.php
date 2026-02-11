<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Table::class);
        $tables = Table::all();
        return view('tables.index', compact('tables'));
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
            'capacity' => 'integer|min:1',
            'status' => 'in:available,occupied,reserved',
        ]);

        $table->update($validated);
        return redirect()->route('tables.index')->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table)
    {
        $this->authorize('delete', $table);
        $table->delete();
        return redirect()->route('tables.index')->with('success', 'Table deleted successfully.');
    }
}
