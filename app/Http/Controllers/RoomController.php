<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::withCount('tables')->orderBy('sort_order')->get();

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $this->authorize('create', Room::class);

        return view('rooms.create');
    }

    public function store(StoreRoomRequest $request)
    {
        $this->authorize('create', Room::class);

        $data = $request->validated();
        $data['sort_order'] = Room::max('sort_order') + 1;

        $room = Room::create($data);

        if ($request->wantsJson()) {
            return response()->json($room, 201);
        }

        return redirect()->route('rooms.index')->with('success', __('Room created successfully.'));
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room);

        return view('rooms.edit', compact('room'));
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $this->authorize('update', $room);

        $room->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json($room);
        }

        return redirect()->route('rooms.index')->with('success', __('Room updated successfully.'));
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);

        $room->tables()->update(['room_id' => null]);
        $room->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('rooms.index')->with('success', __('Room deleted successfully.'));
    }
}
