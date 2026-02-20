<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use App\Http\Requests\StoreShiftRequest;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Shift::class);
        $shifts = Shift::with('user')->latest()->paginate(20);
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        $this->authorize('create', Shift::class);
        $users = User::all();
        return view('shifts.create', compact('users'));
    }

    public function store(StoreShiftRequest $request)
    {
        $this->authorize('create', Shift::class);

        Shift::create($request->validated());
        return redirect()->route('shifts.index')->with('success', 'Shift scheduled successfully.');
    }

    public function destroy(Shift $shift)
    {
        $this->authorize('delete', $shift);
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
    }
}
