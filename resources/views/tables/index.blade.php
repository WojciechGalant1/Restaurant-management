<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Tables Management') }}
                </h2>
                @if(!($isManager ?? false) && !($isHost ?? false))
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('You are viewing tables assigned to you.') }}
                    </p>
                @endif
                @if($isHost ?? false)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Floor plan and guest seating.') }}
                    </p>
                @endif
            </div>
            <div class="flex space-x-2">
                @if($isManager ?? false)
                    <a href="{{ route('rooms.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition flex items-center">
                        <x-heroicon-o-building-office class="w-4 h-4 mr-2" />
                        {{ __('Manage Rooms') }}
                    </a>
                @endif
                @can('create', App\Models\Table::class)
                    <a href="{{ route('tables.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
                        <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                        {{ __('Add New Table') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="tablesPage()" x-cloak>
            <x-flash-message type="success" />
            <x-flash-message type="error" />

            <x-tabs
                :tabs="['grid' => __('Grid View'), 'table' => __('Table View')]"
                default="grid"
                :icons="['grid' => 'heroicon-o-squares-2x2', 'table' => 'heroicon-o-table-cells']"
            />

            {{-- Grid View --}}
            <div x-show="tab === 'grid'">
                {{-- Legend --}}
                <div class="flex flex-wrap gap-4 mb-4 text-sm">
                    <span class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span>
                        {{ __('Available') }}
                    </span>
                    <span class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-red-500 mr-1.5"></span>
                        {{ __('Occupied') }}
                    </span>
                    <span class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-amber-500 mr-1.5"></span>
                        {{ __('Reserved') }}
                    </span>
                </div>

                {{-- Rooms container (outer sortable) --}}
                <div id="rooms-container" class="space-y-6">
                    <template x-for="room in rooms" :key="room.id">
                        <div class="room-section bg-white rounded-lg shadow-sm overflow-hidden" :data-room-id="room.id">
                            {{-- Room header --}}
                            <div class="flex items-center px-4 py-3 border-b" :style="`border-left: 4px solid ${room.color}`">
                                @if($isManager ?? false)
                                <span class="room-drag-handle cursor-grab mr-2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </span>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate" x-text="room.name"></h3>
                                    <p class="text-xs text-gray-500 truncate" x-show="room.description" x-text="room.description"></p>
                                </div>
                                <span class="text-xs text-gray-400 ml-2" x-text="tablesInRoom(room.id).length + ' {{ __('tables') }}'"></span>
                                @if($isManager ?? false)
                                <button @click="editRoom(room)" class="ml-2 text-gray-400 hover:text-indigo-600 transition">
                                    <x-heroicon-o-pencil class="w-4 h-4" />
                                </button>
                                <button @click="deleteRoom(room)" class="ml-1 text-gray-400 hover:text-red-600 transition">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                                @endif
                            </div>

                            {{-- Tables grid inside room --}}
                            <div class="tables-sortable grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 p-4 min-h-[60px]" :data-room-id="room.id">
                                <template x-for="table in tablesInRoom(room.id)" :key="table.id">
                                    <div @click="openModal(table.id)"
                                         :data-table-id="table.id"
                                         :class="'table-card border-2 rounded-xl p-4 cursor-pointer transition-all shadow-sm hover:shadow-md ' + statusStyle(table.status, 'cardBg')">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-2xl font-bold text-gray-800">#<span x-text="table.table_number"></span></span>
                                            <span class="w-3 h-3 rounded-full" :class="statusStyle(table.status, 'dot')"></span>
                                        </div>
                                        <div class="text-xs text-gray-500 mb-1">
                                            <x-heroicon-o-users class="w-3.5 h-3.5 inline -mt-0.5" />
                                            <span x-text="table.capacity"></span> {{ __('seats') }}
                                        </div>
                                        <div class="text-xs font-medium truncate" :class="table.waiter_name ? 'text-indigo-700' : 'text-gray-400'">
                                            <x-heroicon-o-user class="w-3.5 h-3.5 inline -mt-0.5" />
                                            <span x-text="table.waiter_name || '{{ __('Unassigned') }}'"></span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-[10px] font-semibold uppercase tracking-wider" :class="statusStyle(table.status, 'text')" x-text="table.status_label"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="tablesInRoom(room.id).length === 0" class="col-span-full text-center text-gray-400 text-xs py-4 italic">
                                    {{ __('Drag tables here') }}
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Unassigned tables --}}
                <div class="mt-6" x-show="unassignedTables.length > 0 || rooms.length > 0">
                    <div class="bg-gray-50 rounded-lg shadow-sm overflow-hidden">
                        <div class="flex items-center px-4 py-3 border-b border-gray-200">
                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-gray-600">{{ __('Unassigned') }}</h3>
                                <p class="text-xs text-gray-400">{{ __('Tables not assigned to any room') }}</p>
                            </div>
                            <span class="text-xs text-gray-400" x-text="unassignedTables.length + ' {{ __('tables') }}'"></span>
                        </div>
                        <div class="tables-sortable grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 p-4 min-h-[60px]" data-room-id="unassigned">
                            <template x-for="table in unassignedTables" :key="table.id">
                                <div @click="openModal(table.id)"
                                     :data-table-id="table.id"
                                     :class="'table-card border-2 rounded-xl p-4 cursor-pointer transition-all shadow-sm hover:shadow-md ' + statusStyle(table.status, 'cardBg')">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-2xl font-bold text-gray-800">#<span x-text="table.table_number"></span></span>
                                        <span class="w-3 h-3 rounded-full" :class="statusStyle(table.status, 'dot')"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 mb-1">
                                        <x-heroicon-o-users class="w-3.5 h-3.5 inline -mt-0.5" />
                                        <span x-text="table.capacity"></span> {{ __('seats') }}
                                    </div>
                                    <div class="text-xs font-medium truncate" :class="table.waiter_name ? 'text-indigo-700' : 'text-gray-400'">
                                        <x-heroicon-o-user class="w-3.5 h-3.5 inline -mt-0.5" />
                                        <span x-text="table.waiter_name || '{{ __('Unassigned') }}'"></span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="text-[10px] font-semibold uppercase tracking-wider" :class="statusStyle(table.status, 'text')" x-text="table.status_label"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="unassignedTables.length === 0" class="col-span-full text-center text-gray-400 text-xs py-4 italic">
                                {{ __('No unassigned tables') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fallback when no rooms and no tables --}}
                <div x-show="rooms.length === 0 && allTables.length === 0" class="bg-white rounded-lg shadow-sm p-10 text-center text-gray-500 italic">
                    {{ __('No tables found. Click the button above to add your first table.') }}
                </div>

                @if($isManager ?? false)
                {{-- Add room button --}}
                <div class="mt-4">
                    <button @click="showAddRoom = true" class="flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                        <x-heroicon-o-plus-circle class="w-5 h-5 mr-1" />
                        {{ __('Add Room') }}
                    </button>
                </div>
                @endif
            </div>

            {{-- Table View --}}
            <div x-show="tab === 'table'">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Number') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Room') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Capacity') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Waiter') }}</th>
                                    @if($isManager ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="table in allTables" :key="table.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="table.table_number"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <template x-if="table.room_name">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :style="`background-color: ${table.room_color}20; color: ${table.room_color}`" x-text="table.room_name"></span>
                                            </template>
                                            <template x-if="!table.room_name">
                                                <span class="text-gray-400 text-xs">—</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="table.capacity"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': table.status === 'available',
                                                    'bg-red-100 text-red-800': table.status === 'occupied',
                                                    'bg-yellow-100 text-yellow-800': table.status === 'reserved',
                                                }"
                                                x-text="table.status_label">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="table.waiter_name || '{{ __('Unassigned') }}'"></td>
                                        @if($isManager ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2 items-center">
                                                <a :href="`{{ url('tables') }}/${table.id}/edit`" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1 rounded transition">
                                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                                </a>
                                                <form :action="`{{ url('tables') }}/${table.id}`" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this table?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-1 rounded transition">
                                                        <x-heroicon-o-trash class="w-5 h-5" />
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                </template>
                                <tr x-show="allTables.length === 0">
                                    <td colspan="{{ ($isManager ?? false) ? 6 : 5 }}" class="px-6 py-10 text-center text-gray-500 italic">
                                        {{ __('No tables found. Click the button above to add your first table.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Table detail modal --}}
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeModal()">
                <div x-show="modalOpen" x-transition
                     class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <template x-if="modalTable">
                        <div>
                            <div class="px-6 py-4 border-b flex items-center justify-between"
                                 :class="{
                                    'bg-emerald-50': modalTable.status === 'available',
                                    'bg-red-50': modalTable.status === 'occupied',
                                    'bg-amber-50': modalTable.status === 'reserved',
                                 }">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">
                                        {{ __('Table') }} #<span x-text="modalTable.table_number"></span>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <span x-text="modalTable.capacity"></span> {{ __('seats') }}
                                        &middot;
                                        <span class="font-semibold" x-text="modalTable.status_label"></span>
                                    </p>
                                </div>
                                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>

                            <div class="px-6 py-4 space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Assigned Waiter') }}</label>
                                    <p class="text-sm font-semibold mt-0.5" :class="modalTable.waiter_name ? 'text-indigo-700' : 'text-gray-400'" x-text="modalTable.waiter_name || '{{ __('Unassigned') }}'"></p>
                                </div>

                                @if(($isHost ?? false))
                                <div class="space-y-3">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block">{{ __('Change status') }}</label>
                                    <div class="flex gap-2 flex-wrap">
                                        <button type="button" @click="updateTableStatus(modalTable, 'available')"
                                                :disabled="modalTable.status === 'available'"
                                                class="px-3 py-2 rounded-md text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed bg-emerald-100 text-emerald-800 hover:bg-emerald-200">
                                            {{ __('Available') }}
                                        </button>
                                        <button type="button" @click="updateTableStatus(modalTable, 'occupied')"
                                                :disabled="modalTable.status === 'occupied'"
                                                class="px-3 py-2 rounded-md text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed bg-red-100 text-red-800 hover:bg-red-200">
                                            {{ __('Occupied') }}
                                        </button>
                                        <button type="button" @click="updateTableStatus(modalTable, 'reserved')"
                                                :disabled="modalTable.status === 'reserved'"
                                                class="px-3 py-2 rounded-md text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed bg-amber-100 text-amber-800 hover:bg-amber-200">
                                            {{ __('Reserved') }}
                                        </button>
                                    </div>
                                </div>
                                @endif
                                @if($isManager ?? false)
                                <form :action="`{{ url('tables') }}/${modalTable.id}/assign`" method="POST" class="space-y-3">
                                    @csrf
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block">{{ __('Change Assignment') }}</label>

                                    <select name="shift_id" x-model="selectedShiftId"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('Select active shift...') }}</option>
                                        @foreach($activeShifts as $shift)
                                            <option value="{{ $shift->id }}">
                                                {{ $shift->user->name }}
                                                ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="user_id" :value="selectedWaiterId">

                                    <div class="flex space-x-2">
                                        <button type="submit" :disabled="!selectedShiftId"
                                                class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                            {{ __('Assign') }}
                                        </button>
                                        <button type="submit" @click="selectedShiftId = modalTable.shift_id; $nextTick(() => { $el.closest('form').querySelector('[name=user_id]').value = ''; })"
                                                x-show="modalTable.waiter_name && modalTable.shift_id"
                                                class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition">
                                            {{ __('Unassign') }}
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            @if($isManager ?? false)
                            <div class="px-6 py-3 bg-gray-50 border-t flex items-center justify-between">
                                <a :href="`{{ url('tables') }}/${modalTable.id}/edit`" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                                    {{ __('Edit Table') }}
                                </a>
                                <form :action="`{{ url('tables') }}/${modalTable.id}`" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium transition">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </template>
                </div>
            </div>

            @if($isManager ?? false)
            {{-- Add/Edit Room modal --}}
            <div x-show="showAddRoom || editingRoom" x-transition.opacity x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeRoomModal()">
                <div x-show="showAddRoom || editingRoom" x-transition x-cloak class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800" x-text="editingRoom ? '{{ __('Edit Room') }}' : '{{ __('Add Room') }}'"></h3>
                    </div>
                    <form @submit.prevent="submitRoom()" class="px-6 py-4 space-y-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">{{ __('Name') }}</label>
                            <input type="text" x-model="roomForm.name" required class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">{{ __('Description') }}</label>
                            <input type="text" x-model="roomForm.description" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">{{ __('Color') }}</label>
                            <input type="color" x-model="roomForm.color" class="w-full h-10 border-gray-300 rounded-md shadow-sm cursor-pointer" />
                        </div>
                        <div class="flex space-x-2 pt-2">
                            <button type="submit" class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition" x-text="editingRoom ? '{{ __('Update') }}' : '{{ __('Create') }}'"></button>
                            <button type="button" @click="closeRoomModal()" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition">{{ __('Cancel') }}</button>
                        </div>
                        <p x-show="roomError" x-text="roomError" class="text-red-600 text-xs"></p>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    @vite('resources/js/tables-sortable.js')
    <script>
        function tablesPage() {
            const allTablesData = @js($tables->map(fn ($t) => [
                'id' => $t->id,
                'table_number' => $t->table_number,
                'capacity' => $t->capacity,
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'waiter_name' => $t->activeAssignment?->user?->name,
                'waiter_id' => $t->activeAssignment?->user_id,
                'shift_id' => $t->activeAssignment?->shift_id,
                'room_id' => $t->room_id,
                'room_name' => $t->room?->name,
                'room_color' => $t->room?->color,
                'sort_order' => $t->sort_order,
            ]));

            const roomsData = @js($rooms->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'color' => $r->color,
                'sort_order' => $r->sort_order,
            ]));

            const shiftsMap = @js(($activeShifts ?? collect())->mapWithKeys(fn ($s) => [$s->id => $s->user_id]));

            const statusStyles = {
                available: {
                    cardBg: 'bg-emerald-50 border-emerald-300 hover:border-emerald-500',
                    dot: 'bg-emerald-500',
                    text: 'text-emerald-700',
                },
                occupied: {
                    cardBg: 'bg-red-50 border-red-300 hover:border-red-500',
                    dot: 'bg-red-500',
                    text: 'text-red-700',
                },
                reserved: {
                    cardBg: 'bg-amber-50 border-amber-300 hover:border-amber-500',
                    dot: 'bg-amber-500',
                    text: 'text-amber-700',
                },
            };

            const isManager = @js($isManager ?? false);
            const isHost = @js($isHost ?? false);
            const reorderUrl = @js(route('tables.reorder'));
            const updateStatusUrl = @js(route('tables.update-status', ['table' => ':id']));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            return {
                tab: 'grid',
                isManager,
                isHost,
                modalOpen: false,
                modalTable: null,
                selectedShiftId: '',
                allTables: [...allTablesData],
                rooms: [...roomsData],

                showAddRoom: false,
                editingRoom: null,
                roomForm: { name: '', description: '', color: '#6366f1' },
                roomError: '',

                get unassignedTables() {
                    return this.allTables.filter(t => !t.room_id).sort((a, b) => a.sort_order - b.sort_order);
                },

                tablesInRoom(roomId) {
                    return this.allTables.filter(t => t.room_id === roomId).sort((a, b) => a.sort_order - b.sort_order);
                },

                get selectedWaiterId() {
                    return this.selectedShiftId ? (shiftsMap[this.selectedShiftId] || '') : '';
                },

                statusStyle(status, prop) {
                    return (statusStyles[status] || statusStyles.available)[prop] || '';
                },

                openModal(tableId) {
                    this.modalTable = this.allTables.find(t => t.id === tableId) || null;
                    this.selectedShiftId = this.modalTable?.shift_id || '';
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                async updateTableStatus(table, newStatus) {
                    if (!csrfToken) return;
                    try {
                        const url = updateStatusUrl.replace(':id', table.id);
                        const res = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });
                        if (!res.ok) throw new Error('Request failed');
                        const statusLabels = { available: '{{ __("Available") }}', occupied: '{{ __("Occupied") }}', reserved: '{{ __("Reserved") }}' };
                        const newLabel = statusLabels[newStatus] || newStatus;
                        const idx = this.allTables.findIndex(t => t.id === table.id);
                        if (idx !== -1) {
                            this.allTables[idx] = { ...this.allTables[idx], status: newStatus, status_label: newLabel };
                        }
                        if (this.modalTable && this.modalTable.id === table.id) {
                            this.modalTable = { ...this.modalTable, status: newStatus, status_label: newLabel };
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                editRoom(room) {
                    this.editingRoom = room;
                    this.roomForm = { name: room.name, description: room.description || '', color: room.color };
                    this.roomError = '';
                },

                closeRoomModal() {
                    this.showAddRoom = false;
                    this.editingRoom = null;
                    this.roomForm = { name: '', description: '', color: '#6366f1' };
                    this.roomError = '';
                },

                async submitRoom() {
                    this.roomError = '';
                    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };
                    try {
                        if (this.editingRoom) {
                            const res = await fetch(`/rooms/${this.editingRoom.id}`, { method: 'PATCH', headers, body: JSON.stringify(this.roomForm) });
                            if (!res.ok) { const d = await res.json(); this.roomError = d.message || 'Error'; return; }
                            const updated = await res.json();
                            const idx = this.rooms.findIndex(r => r.id === updated.id);
                            if (idx !== -1) this.rooms[idx] = { ...this.rooms[idx], ...updated };
                            this.allTables.forEach(t => { if (t.room_id === updated.id) { t.room_name = updated.name; t.room_color = updated.color; } });
                        } else {
                            const res = await fetch('/rooms', { method: 'POST', headers, body: JSON.stringify(this.roomForm) });
                            if (!res.ok) { const d = await res.json(); this.roomError = d.message || 'Error'; return; }
                            const created = await res.json();
                            this.rooms.push({ id: created.id, name: created.name, description: created.description, color: created.color, sort_order: created.sort_order });
                            this.$nextTick(() => { if (window.reinitTableSortables) window.reinitTableSortables(this); });
                        }
                        this.closeRoomModal();
                    } catch (e) {
                        this.roomError = e.message;
                    }
                },

                async deleteRoom(room) {
                    if (!confirm('{{ __('Tables in this room will become unassigned. Continue?') }}')) return;
                    const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };
                    await fetch(`/rooms/${room.id}`, { method: 'DELETE', headers });
                    this.rooms = this.rooms.filter(r => r.id !== room.id);
                    this.allTables.forEach(t => { if (t.room_id === room.id) { t.room_id = null; t.room_name = null; t.room_color = null; } });
                },

                applyTableMove({ fromRoomId, toRoomId, tableId, oldIndex, newIndex }) {
                    const parseId = (id) => id === 'unassigned' ? null : parseInt(id);
                    const fromId = parseId(fromRoomId);
                    const toId = parseId(toRoomId);

                    const getTablesFor = (rid) => this.allTables
                        .filter(t => t.room_id === rid)
                        .sort((a, b) => a.sort_order - b.sort_order);

                    const fromTables = getTablesFor(fromId);

                    if (fromId === toId) {
                        const [moved] = fromTables.splice(oldIndex, 1);
                        fromTables.splice(newIndex, 0, moved);
                        fromTables.forEach((t, i) => { t.sort_order = i; });
                    } else {
                        const [moved] = fromTables.splice(oldIndex, 1);
                        fromTables.forEach((t, i) => { t.sort_order = i; });

                        const toTables = getTablesFor(toId);
                        toTables.splice(newIndex, 0, moved);
                        toTables.forEach((t, i) => { t.sort_order = i; });

                        moved.room_id = toId;
                        if (toId !== null) {
                            const r = this.rooms.find(x => x.id === toId);
                            if (r) { moved.room_name = r.name; moved.room_color = r.color; }
                        } else {
                            moved.room_name = null; moved.room_color = null;
                        }
                    }

                    this.persistOrder();
                    this.$nextTick(() => {
                        if (window.reinitTableSortables) window.reinitTableSortables(this);
                    });
                },

                applyRoomMove(oldIndex, newIndex) {
                    const [moved] = this.rooms.splice(oldIndex, 1);
                    this.rooms.splice(newIndex, 0, moved);
                    this.rooms.forEach((r, i) => { r.sort_order = i; });

                    this.persistOrder();
                    this.$nextTick(() => {
                        if (window.initTablesSortables) window.initTablesSortables(this);
                    });
                },

                persistOrder() {
                    const roomsPayload = this.rooms.map((r, rIdx) => ({
                        id: r.id,
                        sort_order: rIdx,
                        tables: this.tablesInRoom(r.id).map((t, tIdx) => ({
                            id: t.id,
                            sort_order: tIdx,
                        })),
                    }));

                    const unassigned = this.unassignedTables.map((t, idx) => ({
                        id: t.id,
                        sort_order: idx,
                    }));

                    fetch(reorderUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ rooms: roomsPayload, unassigned }),
                    });
                },

                init() {
                    if (window.Echo) {
                        window.Echo.private('tables')
                            .listen('.TableStatusUpdated', (e) => {
                                const idx = this.allTables.findIndex(t => t.id === e.id);
                                if (idx !== -1) {
                                    this.allTables[idx] = { ...this.allTables[idx], ...e };
                                }
                                if (this.modalTable && this.modalTable.id === e.id) {
                                    this.modalTable = { ...this.modalTable, ...e };
                                }
                            });
                    }

                    this.$nextTick(() => {
                        if (window.initTablesSortables) {
                            window.initTablesSortables(this);
                        }
                    });
                },
            };
        }
    </script>
</x-app-layout>
