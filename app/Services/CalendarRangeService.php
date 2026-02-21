<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarRangeService
{
    /**
     * Parse FullCalendar view range from request (start/end query params).
     *
     * @return array{0: Carbon|null, 1: Carbon|null} [viewStart, viewEnd]
     */
    public function fromRequest(Request $request): array
    {
        $viewStart = $request->filled('start') ? Carbon::parse($request->input('start')) : null;
        $viewEnd = $request->filled('end') ? Carbon::parse($request->input('end')) : null;

        return [$viewStart, $viewEnd];
    }
}
