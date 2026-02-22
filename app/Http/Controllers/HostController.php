<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Shift;
use App\Models\TableAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HostController extends Controller
{
    /** Default reservation block duration in minutes for the timeline. */
    public const DEFAULT_DURATION_MINUTES = 120;

    /** Timeline range: start and end hour (0–24). */
    public const HOUR_START = 10;

    public const HOUR_END = 23;

    /**
     * Today's reservations by room — timeline view for Host (and Manager).
     * Columns = rooms, rows = time axis, events = reservations absolutely positioned.
     */
    public function today(Request $request)
    {
        $user = $request->user();
        if (! in_array($user->role, [UserRole::Manager, UserRole::Host])) {
            abort(403);
        }

        $today = Carbon::today();
        $rooms = Room::orderBy('sort_order')->get();

        $reservations = Reservation::with(['table.room'])
            ->whereDate('reservation_date', $today)
            ->whereNotIn('status', [ReservationStatus::Cancelled, ReservationStatus::NoShow])
            ->orderBy('reservation_time')
            ->get();

        $eventsByRoom = [];
        foreach ($rooms as $room) {
            $eventsByRoom[$room->id] = [
                'room'   => $room,
                'events' => [],
                'staff'  => [],
            ];
        }
        $eventsByRoom['unassigned'] = ['room' => null, 'events' => [], 'staff' => []];

        $hourStart = self::HOUR_START;
        $hourEnd = self::HOUR_END;
        $dayStart = $today->copy()->setTime($hourStart, 0, 0);

        foreach ($reservations as $r) {
            $table = $r->table;
            $roomId = $table && $table->room_id ? $table->room_id : 'unassigned';
            if ($roomId !== 'unassigned' && ! isset($eventsByRoom[$roomId])) {
                continue;
            }

            $timePart = $r->reservation_time instanceof \Carbon\Carbon
                ? $r->reservation_time->format('H:i:s')
                : (is_string($r->reservation_time) ? $r->reservation_time : \Carbon\Carbon::parse($r->reservation_time)->format('H:i:s'));
            $start = Carbon::parse($r->reservation_date->format('Y-m-d') . ' ' . $timePart);
            $durationMinutes = (int) (self::DEFAULT_DURATION_MINUTES);
            $end = $start->copy()->addMinutes($durationMinutes);

            $eventsByRoom[$roomId]['events'][] = [
                'reservation' => $r,
                'start'       => $start,
                'end'         => $end,
                'duration'    => $durationMinutes,
            ];
        }

        // Staff assigned to tables per room (today's shifts) — show in room columns
        $assignments = TableAssignment::with(['shift', 'user', 'table'])
            ->whereHas('shift', fn ($q) => $q->whereDate('date', $today))
            ->get();

        $staffByRoom = [];
        foreach ($assignments as $a) {
            $table = $a->table;
            if (! $table) {
                continue;
            }
            $roomId = $table->room_id ?: 'unassigned';
            if ($roomId !== 'unassigned' && ! isset($eventsByRoom[$roomId])) {
                continue;
            }
            $shift = $a->shift;
            $start = Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time);
            $end = Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->end_time);
            if ($end <= $start) {
                $end->addDay();
            }
            $key = $roomId . '_' . $a->user_id;
            if (! isset($staffByRoom[$key])) {
                $staffByRoom[$key] = [
                    'room_id' => $roomId,
                    'user'    => $a->user,
                    'start'   => $start,
                    'end'     => $end,
                ];
            } else {
                // Merge time range if same user in same room (multiple tables)
                $staffByRoom[$key]['start'] = $staffByRoom[$key]['start']->copy()->min($start);
                $staffByRoom[$key]['end'] = $staffByRoom[$key]['end']->copy()->max($end);
            }
        }
        foreach ($staffByRoom as $row) {
            $eventsByRoom[$row['room_id']]['staff'][] = [
                'user'  => $row['user'],
                'start' => $row['start'],
                'end'   => $row['end'],
            ];
        }

        // Assign lanes so overlapping shifts in the same room sit side by side
        foreach ($eventsByRoom as $roomId => &$roomData) {
            $staffList = $roomData['staff'];
            if (empty($staffList)) {
                continue;
            }
            usort($staffList, fn ($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);
            $laneEnds = [];
            foreach ($staffList as &$ev) {
                $placed = false;
                foreach ($laneEnds as $lane => $end) {
                    if ($ev['start']->gte($end)) {
                        $laneEnds[$lane] = $ev['end'];
                        $ev['lane_index'] = $lane;
                        $placed = true;
                        break;
                    }
                }
                if (! $placed) {
                    $ev['lane_index'] = count($laneEnds);
                    $laneEnds[] = $ev['end'];
                }
            }
            unset($ev);
            $totalLanes = count($laneEnds);
            foreach ($staffList as &$ev) {
                $ev['total_lanes'] = $totalLanes;
            }
            unset($ev);
            $roomData['staff'] = $staffList;
        }
        unset($roomData);

        $totalMinutes = ($hourEnd - $hourStart) * 60;
        $pxPerMinute = 1;
        $dayEnd = $today->copy()->setTime($hourEnd, 0, 0);

        // Staff currently on shift (active now)
        $activeShifts = Shift::with('user')->activeNow()->get();
        $staffOnShiftNow = $activeShifts->pluck('user')->unique('id')->values();

        return view('host.today', [
            'rooms'           => $rooms,
            'eventsByRoom'    => $eventsByRoom,
            'hourStart'       => $hourStart,
            'hourEnd'         => $hourEnd,
            'totalMinutes'    => $totalMinutes,
            'pxPerMinute'     => $pxPerMinute,
            'dayStart'        => $dayStart,
            'dayEnd'          => $dayEnd,
            'staffOnShiftNow' => $staffOnShiftNow,
        ]);
    }
}
