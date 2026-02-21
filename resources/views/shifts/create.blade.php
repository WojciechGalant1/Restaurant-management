@php
    $availabilityUrl = route('shifts.availability');
    $coverageUrl = route('shifts.coverage');
    $weekdayLabels = [1 => __('Mon'), 2 => __('Tue'), 3 => __('Wed'), 4 => __('Thu'), 5 => __('Fri'), 6 => __('Sat'), 7 => __('Sun')];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Schedule New Shift') }}
            </h2>
            <a href="{{ route('shifts.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="shiftCreateForm({
        availabilityUrl: @js($availabilityUrl),
        coverageUrl: @js($coverageUrl),
        usersByRole: @js($usersByRole),
        hoursPerUser: @js($hoursPerUser),
        maxHoursPerWeek: {{ $maxHoursPerWeek }},
        weekdayLabels: @js($weekdayLabels),
        initialDate: @js(old('date', request('date', date('Y-m-d')))),
        initialShiftType: @js(old('shift_type', 'morning')),
        initialStartTime: @js(old('start_time', \App\Enums\ShiftType::Morning->startTime())),
        initialEndTime: @js(old('end_time', \App\Enums\ShiftType::Morning->endTime())),
        initialNotes: @js(old('notes', '')),
        shiftTypeTimes: @js(collect(\App\Enums\ShiftType::cases())->mapWithKeys(fn ($t) => [$t->value => ['start' => $t->startTime(), 'end' => $t->endTime()]])->all()),
    })">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('shifts.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @csrf

                {{-- Left: Form 2/3 --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Staff') }}</h3>
                        <p class="text-sm text-gray-500 mb-3">{{ __('Select one or more people for this shift.') }}</p>

                        @foreach(['kitchen' => __('Kitchen'), 'floor' => __('Floor'), 'bar' => __('Bar'), 'management' => __('Management')] as $roleKey => $roleLabel)
                            @if(($usersByRole[$roleKey] ?? collect())->isNotEmpty())
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $roleLabel }}</h4>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($usersByRole[$roleKey] as $user)
                                            @php
                                                $hours = $hoursPerUser[$user->id] ?? 0;
                                            @endphp
                                            <label class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    x-model="selectedUserIds"
                                                    @if(in_array($user->id, (array) old('user_ids', []))) checked @endif>
                                                <span class="ml-2 text-sm">
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                    <span class="text-gray-500">({{ $hours }}/{{ $maxHoursPerWeek }}h)</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        <x-input-error :messages="$errors->get('user_ids')" class="mt-2" />
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Date & time') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="date" :value="__('Shift date')" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" x-model="baseDate" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="shift_type" :value="__('Shift type')" />
                                <select id="shift_type" name="shift_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    x-model="shiftType" @change="applyShiftTypeTimes()">
                                    @foreach($shiftTypes as $type)
                                        <option value="{{ $type->value }}" data-start="{{ $type->startTime() }}" data-end="{{ $type->endTime() }}">
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="start_time" :value="__('Start time')" />
                                <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" x-model="startTime" required />
                            </div>
                            <div>
                                <x-input-label for="end_time" :value="__('End time')" />
                                <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" x-model="endTime" required />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Repeat for weekdays') }}</h3>
                        <p class="text-sm text-gray-500 mb-3">{{ __('Create the same shift on selected days of the week (week of the chosen date).') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($weekdayLabels as $num => $label)
                                <label class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                                    <input type="checkbox" name="replicate_days[]" value="{{ $num }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="replicateDays">
                                    <span class="ml-2 text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2" x-model="notes">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <x-primary-button type="submit">
                            {{ __('Schedule shift(s)') }}
                        </x-primary-button>
                    </div>
                </div>

                {{-- Right: Summary 1/3 --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Daily coverage') }}</h3>
                        <template x-if="coverageDates.length === 0">
                            <p class="text-sm text-gray-500">{{ __('Select a date to see coverage.') }}</p>
                        </template>
                        <template x-if="coverageDates.length > 0 && Object.keys(coverage).length === 0 && !coverageLoading">
                            <p class="text-sm text-gray-500">{{ __('No shifts yet on selected day(s).') }}</p>
                        </template>
                        <template x-if="coverageLoading">
                            <p class="text-sm text-gray-500">{{ __('Loading…') }}</p>
                        </template>
                        <div class="space-y-2 text-sm" x-show="Object.keys(coverage).length > 0 && !coverageLoading">
                            <template x-for="(counts, date) in coverage" :key="date">
                                <div class="border-b border-gray-100 pb-2 last:border-0" x-show="date.match(/^\d{4}-\d{2}-\d{2}$/)">
                                    <span class="font-medium" x-text="formatDate(date)"></span>
                                    <div class="text-gray-600 mt-0.5">
                                        <span x-show="counts.chef > 0"><span x-text="counts.chef"></span> {{ __('Chefs') }}</span>
                                        <span x-show="counts.waiter > 0" class="ml-2"><span x-text="counts.waiter"></span> {{ __('Waiters') }}</span>
                                        <span x-show="counts.bartender > 0" class="ml-2"><span x-text="counts.bartender"></span> {{ __('Bartenders') }}</span>
                                        <span x-show="counts.manager > 0" class="ml-2"><span x-text="counts.manager"></span> {{ __('Managers') }}</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Alerts') }}</h4>
                            <div class="space-y-1 text-sm">
                                <template x-for="(msg, i) in alertMessages" :key="i">
                                    <p class="text-red-600" x-text="msg"></p>
                                </template>
                                <template x-if="alertMessages.length === 0 && coverageDates.length > 0 && !coverageLoading">
                                    <p class="text-gray-500">{{ __('No alerts.') }}</p>
                                </template>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200" x-show="selectedUserIds.length > 0 && baseDate">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('Staff availability') }}</h4>
                            <template x-if="availabilityLoading">
                                <p class="text-sm text-gray-500">{{ __('Loading…') }}</p>
                            </template>
                            <div class="space-y-2 text-sm" x-show="!availabilityLoading && Object.keys(availability).length > 0">
                                <template x-for="(data, uid) in availability" :key="uid">
                                    <div class="p-2 rounded" x-show="data && typeof data === 'object' && 'hours_week' in data"
                                        :class="data.conflict || data.exceeds_day || data.exceeds_week ? 'bg-red-50 text-red-800' : 'bg-gray-50 text-gray-700'">
                                        <span x-text="getUserName(uid)"></span>
                                        <span x-show="data.conflict" class="block text-xs mt-0.5"> {{ __('Already has a shift on this day.') }} </span>
                                        <span x-show="data.exceeds_week" class="block text-xs mt-0.5"> {{ __('Would exceed :max h/week.', ['max' => $maxHoursPerWeek]) }} </span>
                                        <span x-show="!data.conflict && !data.exceeds_week" class="block text-xs mt-0.5" x-text="(data.hours_week ?? 0) + 'h this week'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('shiftCreateForm', (config) => ({
                availabilityUrl: config.availabilityUrl,
                coverageUrl: config.coverageUrl,
                usersByRole: config.usersByRole,
                hoursPerUser: config.hoursPerUser || {},
                maxHoursPerWeek: config.maxHoursPerWeek || 40,
                weekdayLabels: config.weekdayLabels || {},
                baseDate: config.initialDate,
                shiftType: config.initialShiftType,
                startTime: config.initialStartTime,
                endTime: config.initialEndTime,
                notes: config.initialNotes,
                shiftTypeTimes: config.shiftTypeTimes || {},
                selectedUserIds: @js((array) old('user_ids', [])),
                replicateDays: @js((array) old('replicate_days', [])),
                availability: {},
                availabilityLoading: false,
                coverage: {},
                coverageLoading: false,

                get coverageDates() {
                    if (!this.baseDate) return [];
                    const base = new Date(this.baseDate + 'T12:00:00');
                    const day = base.getDay();
                    const monday = new Date(base);
                    monday.setDate(base.getDate() - (day === 0 ? 6 : day - 1));
                    const dates = [];
                    if (this.replicateDays.length === 0) {
                        dates.push(this.baseDate);
                    } else {
                        this.replicateDays.forEach(d => {
                            const d2 = new Date(monday);
                            d2.setDate(monday.getDate() + (Number(d) - 1));
                            dates.push(d2.toISOString().slice(0, 10));
                        });
                    }
                    return dates.sort();
                },

                get alertMessages() {
                    const msg = [];
                    if (this.coverageDates.length === 0) return msg;
                    this.coverageDates.forEach(date => {
                        const c = this.coverage[date];
                        if (!c) return;
                        if (c.chef === 0) msg.push(this.formatDate(date) + ': {{ __("No chef assigned") }}');
                    });
                    return msg;
                },

                formatDate(iso) {
                    const d = new Date(iso + 'T12:00:00');
                    return d.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
                },

                getUserName(uid) {
                    const id = String(uid);
                    for (const role of Object.values(this.usersByRole)) {
                        for (const u of role) {
                            if (String(u.id) === id) return (u.first_name || '') + ' ' + (u.last_name || '');
                        }
                    }
                    return id;
                },

                applyShiftTypeTimes() {
                    const t = this.shiftTypeTimes[this.shiftType];
                    if (t) {
                        this.startTime = t.start;
                        this.endTime = t.end;
                    }
                },

                async fetchAvailability() {
                    if (this.selectedUserIds.length === 0 || !this.baseDate) {
                        this.availability = {};
                        return;
                    }
                    this.availabilityLoading = true;
                    try {
                        const url = this.availabilityUrl + '?date=' + encodeURIComponent(this.baseDate) + '&user_ids[]=' + this.selectedUserIds.join('&user_ids[]=');
                        const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await r.json();
                        this.availability = r.ok && data && typeof data === 'object' && !data.message ? data : {};
                    } catch (e) {
                        this.availability = {};
                    }
                    this.availabilityLoading = false;
                },

                async fetchCoverage() {
                    if (this.coverageDates.length === 0) {
                        this.coverage = {};
                        return;
                    }
                    this.coverageLoading = true;
                    try {
                        const url = this.coverageUrl + '?dates=' + this.coverageDates.join(',');
                        const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await r.json();
                        this.coverage = r.ok && data && typeof data === 'object' && !data.message ? data : {};
                    } catch (e) {
                        this.coverage = {};
                    }
                    this.coverageLoading = false;
                },

                init() {
                    this.$watch('baseDate', () => { this.fetchAvailability(); this.fetchCoverage(); });
                    this.$watch('replicateDays', () => this.fetchCoverage(), { deep: true });
                    this.$watch('selectedUserIds', () => this.fetchAvailability(), { deep: true });
                    this.fetchCoverage();
                    this.fetchAvailability();
                },
            }));
        });
    </script>
</x-app-layout>
