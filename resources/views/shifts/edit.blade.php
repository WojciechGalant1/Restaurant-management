<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Edit Shift')">
            <x-slot name="action">
                <a href="{{ route('shifts.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('shifts.update', $shift) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="user_id" :value="__('Staff Member')" />
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Select Staff --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $shift->user_id) == $user->id)>{{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role->value) }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="date" :value="__('Shift Date')" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $shift->date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="shift_type" :value="__('Shift Type')" />
                            <select id="shift_type" name="shift_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($shiftTypes as $type)
                                    <option value="{{ $type->value }}"
                                        data-start="{{ $type->startTime() }}"
                                        data-end="{{ $type->endTime() }}"
                                        @selected(old('shift_type', $shift->shift_type->value) === $type->value)>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('shift_type')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" />
                                <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i'))" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_time" :value="__('End Time')" />
                                <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i'))" required />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('notes', $shift->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            {{ __('Update Shift') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('shift_type').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const startInput = document.getElementById('start_time');
            const endInput = document.getElementById('end_time');

            if (selected.dataset.start) {
                startInput.value = selected.dataset.start;
            }
            if (selected.dataset.end) {
                endInput.value = selected.dataset.end;
            }
        });
    </script>
    @endpush
</x-app-layout>
