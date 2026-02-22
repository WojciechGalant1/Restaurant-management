<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Today') . ' — ' . $dayStart->translatedFormat('l d F Y')"
            :actionUrl="route('reservations.create')"
            :actionLabel="__('New Reservation')"
        />
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <x-flash-message type="success" />
            <x-flash-message type="error" />

            {{-- Timeline: first column = time, then one column per room --}}
            @php
                $showUnassigned = isset($eventsByRoom['unassigned']) && count($eventsByRoom['unassigned']['events']) > 0;
                $roomCols = $rooms->count() + ($showUnassigned ? 1 : 0);
                $gridCols = 'minmax(4rem, 4rem) ' . str_repeat('minmax(10rem, 1fr) ', max(1, $roomCols));
                $trackHeight = $totalMinutes * $pxPerMinute;
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-x-auto">
                {{-- Header row: empty corner + room names --}}
                <div class="grid border-b border-gray-200 sticky top-0 bg-white z-10" style="grid-template-columns: {{ $gridCols }};">
                    <div class="p-2 border-r border-gray-200 font-medium text-gray-500 text-sm">{{ __('Time') }}</div>
                    @foreach($rooms as $room)
                        <div class="p-3 border-r border-gray-200 font-semibold text-gray-800" style="border-left: 3px solid {{ $room->color ?? '#6366f1' }};">
                            {{ $room->name }}
                        </div>
                    @endforeach
                    @if($showUnassigned)
                        <div class="p-3 font-semibold text-gray-500">{{ __('Unassigned') }}</div>
                    @endif
                </div>

                {{-- Body: time column + room columns with absolute events --}}
                <div class="grid border-b border-gray-100" style="grid-template-columns: {{ $gridCols }};">
                    {{-- Time labels column --}}
                    <div class="border-r border-gray-200 relative" style="height: {{ $trackHeight }}px;">
                        @for($h = $hourStart; $h < $hourEnd; $h++)
                            <div class="absolute left-0 right-0 text-xs text-gray-400 pr-1 text-right" style="top: {{ ($h - $hourStart) * 60 * $pxPerMinute }}px;">
                                {{ sprintf('%02d:00', $h) }}
                            </div>
                        @endfor
                    </div>

                    {{-- Room columns --}}
                    @foreach($rooms as $room)
                        @php $data = $eventsByRoom[$room->id] ?? ['room' => $room, 'events' => [], 'staff' => []]; @endphp
                        <div class="border-r border-gray-100 relative" style="height: {{ $trackHeight }}px; min-width: 10rem;">
                            {{-- Staff blocks (kelner / assigned waiter) in this room — side by side when overlapping --}}
                            @foreach($data['staff'] ?? [] as $staffEv)
                                @php
                                    $visibleStart = $staffEv['start']->copy()->max($dayStart);
                                    $visibleEnd = $staffEv['end']->copy()->min($dayEnd);
                                    $staffVisible = $visibleStart->lt($visibleEnd);
                                    if ($staffVisible) {
                                        $staffTopPx = (int) $dayStart->diffInMinutes($visibleStart) * $pxPerMinute;
                                        $staffHeightPx = (int) $visibleStart->diffInMinutes($visibleEnd) * $pxPerMinute;
                                        $lanes = (int) ($staffEv['total_lanes'] ?? 1);
                                        $laneIdx = (int) ($staffEv['lane_index'] ?? 0);
                                        $staffLeft = $lanes <= 1 ? '4px' : 'calc(' . ($laneIdx / $lanes * 100) . '% + 2px)';
                                        $staffWidth = $lanes <= 1 ? 'calc(100% - 8px)' : 'calc(' . (100 / $lanes) . '% - 4px)';
                                    }
                                @endphp
                                @if($staffVisible)
                                <div class="absolute rounded border-2 border-dashed border-gray-400 bg-gray-100/90 text-gray-700 text-xs overflow-hidden"
                                     style="top: {{ $staffTopPx }}px; height: {{ max(20, $staffHeightPx - 2) }}px; left: {{ $staffLeft }}; width: {{ $staffWidth }};"
                                     title="{{ $staffEv['user']->first_name }} {{ $staffEv['user']->last_name }} — {{ $staffEv['start']->format('H:i') }}–{{ $staffEv['end']->format('H:i') }}">
                                    <span class="font-semibold block truncate px-1 pt-0.5">{{ $staffEv['user']->first_name }} {{ $staffEv['user']->last_name }}</span>
                                    <span class="block truncate px-1 opacity-80">{{ $staffEv['start']->format('H:i') }} – {{ $staffEv['end']->format('H:i') }}</span>
                                </div>
                                @endif
                            @endforeach
                            @foreach($data['events'] as $ev)
                                @php
                                    $startMin = $ev['start']->diffInMinutes($dayStart);
                                    $topPx = $startMin * $pxPerMinute;
                                    $heightPx = $ev['duration'] * $pxPerMinute;
                                @endphp
                                <a href="{{ route('reservations.edit', $ev['reservation']) }}"
                                   class="absolute left-1 right-1 rounded-md shadow-sm border overflow-hidden block text-white text-xs hover:opacity-95 transition"
                                   style="top: {{ $topPx }}px; height: {{ max(24, $heightPx - 2) }}px; background: {{ $room->color ?? '#6366f1' }};"
                                   title="{{ $ev['reservation']->customer_name }} — {{ $ev['start']->format('H:i') }} ({{ $ev['reservation']->party_size }} {{ __('guests') }})">
                                    <span class="font-semibold block truncate px-1 pt-0.5">{{ $ev['reservation']->customer_name }}</span>
                                    <span class="block truncate px-1">{{ $ev['start']->format('H:i') }} · {{ $ev['reservation']->table ? __('Table') . ' ' . $ev['reservation']->table->table_number : '' }} · {{ $ev['reservation']->party_size }} {{ __('guests') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach

                    @if($showUnassigned)
                        <div class="relative bg-gray-50/50" style="height: {{ $trackHeight }}px; min-width: 10rem;">
                            @foreach($eventsByRoom['unassigned']['staff'] ?? [] as $staffEv)
                                @php
                                    $visibleStart = $staffEv['start']->copy()->max($dayStart);
                                    $visibleEnd = $staffEv['end']->copy()->min($dayEnd);
                                    $staffVisible = $visibleStart->lt($visibleEnd);
                                    if ($staffVisible) {
                                        $staffTopPx = (int) $dayStart->diffInMinutes($visibleStart) * $pxPerMinute;
                                        $staffHeightPx = (int) $visibleStart->diffInMinutes($visibleEnd) * $pxPerMinute;
                                        $lanes = (int) ($staffEv['total_lanes'] ?? 1);
                                        $laneIdx = (int) ($staffEv['lane_index'] ?? 0);
                                        $staffLeft = $lanes <= 1 ? '4px' : 'calc(' . ($laneIdx / $lanes * 100) . '% + 2px)';
                                        $staffWidth = $lanes <= 1 ? 'calc(100% - 8px)' : 'calc(' . (100 / $lanes) . '% - 4px)';
                                    }
                                @endphp
                                @if($staffVisible)
                                <div class="absolute rounded border-2 border-dashed border-gray-400 bg-gray-100/90 text-gray-700 text-xs overflow-hidden"
                                     style="top: {{ $staffTopPx }}px; height: {{ max(20, $staffHeightPx - 2) }}px; left: {{ $staffLeft }}; width: {{ $staffWidth }};"
                                     title="{{ $staffEv['user']->first_name }} {{ $staffEv['user']->last_name }} — {{ $staffEv['start']->format('H:i') }}–{{ $staffEv['end']->format('H:i') }}">
                                    <span class="font-semibold block truncate px-1 pt-0.5">{{ $staffEv['user']->first_name }} {{ $staffEv['user']->last_name }}</span>
                                    <span class="block truncate px-1 opacity-80">{{ $staffEv['start']->format('H:i') }} – {{ $staffEv['end']->format('H:i') }}</span>
                                </div>
                                @endif
                            @endforeach
                            @foreach($eventsByRoom['unassigned']['events'] as $ev)
                                @php
                                    $startMin = $ev['start']->diffInMinutes($dayStart);
                                    $topPx = $startMin * $pxPerMinute;
                                    $heightPx = $ev['duration'] * $pxPerMinute;
                                @endphp
                                <a href="{{ route('reservations.edit', $ev['reservation']) }}"
                                   class="absolute left-1 right-1 rounded-md shadow-sm border overflow-hidden block bg-gray-400 text-white text-xs hover:opacity-95 transition"
                                   style="top: {{ $topPx }}px; height: {{ max(24, $heightPx - 2) }}px;"
                                   title="{{ $ev['reservation']->customer_name }} — {{ $ev['start']->format('H:i') }}">
                                    <span class="font-semibold block truncate px-1 pt-0.5">{{ $ev['reservation']->customer_name }}</span>
                                    <span class="block truncate px-1">{{ $ev['start']->format('H:i') }} · {{ $ev['reservation']->table ? __('Table') . ' ' . $ev['reservation']->table->table_number : '' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <p class="mt-3 text-sm text-gray-500">{{ __('Click a block to edit the reservation. Default slot length: :minutes min.', ['minutes' => \App\Http\Controllers\HostController::DEFAULT_DURATION_MINUTES]) }}</p>

            {{-- Staff currently on shift --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Currently on shift') }}</h3>
                @if($staffOnShiftNow->isNotEmpty())
                    <ul class="flex flex-wrap gap-2">
                        @foreach($staffOnShiftNow as $u)
                            <li class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-800 text-sm">
                                <span class="font-medium">{{ $u->first_name }} {{ $u->last_name }}</span>
                                <span class="text-indigo-600 text-xs">({{ $u->role->label() }})</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">{{ __('No one is currently on shift.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
