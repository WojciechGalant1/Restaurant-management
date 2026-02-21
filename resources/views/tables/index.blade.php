<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Tables Management') }}
                </h2>
                @if(!($isManager ?? false))
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('You are viewing tables assigned to you.') }}
                    </p>
                @endif
            </div>
            @can('create', App\Models\Table::class)
                <a href="{{ route('tables.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Add New Table') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="tablesPage()" x-cloak>
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm sm:rounded-r-lg" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Tab navigation --}}
            <div class="mb-4 border-b border-gray-200">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button @click="tab = 'grid'"
                        :class="tab === 'grid' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 mr-2" />
                        {{ __('Grid View') }}
                    </button>
                    <button @click="tab = 'table'"
                        :class="tab === 'table' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center transition">
                        <x-heroicon-o-table-cells class="w-4 h-4 mr-2" />
                        {{ __('Table View') }}
                    </button>
                </nav>
            </div>

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

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="table in tables" :key="table.id">
                        <div @click="openModal(table.id)"
                             :class="'border-2 rounded-xl p-4 cursor-pointer transition-all shadow-sm hover:shadow-md ' + statusStyle(table.status, 'cardBg')">
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
                </div>

                <div x-show="tables.length === 0" class="bg-white rounded-lg shadow-sm p-10 text-center text-gray-500 italic">
                    {{ __('No tables found. Click the button above to add your first table.') }}
                </div>
            </div>

            {{-- Table View --}}
            <div x-show="tab === 'table'">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waiter</th>
                                    @if($isManager ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="table in tables" :key="table.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="table.table_number"></td>
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
                                <tr x-show="tables.length === 0">
                                    <td colspan="{{ ($isManager ?? false) ? 5 : 4 }}" class="px-6 py-10 text-center text-gray-500 italic">
                                        {{ __('No tables found. Click the button above to add your first table.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal --}}
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeModal()">
                <div x-show="modalOpen" x-transition
                     class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <template x-if="modalTable">
                        <div>
                            {{-- Modal Header --}}
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

                            {{-- Modal Body --}}
                            <div class="px-6 py-4 space-y-4">
                                {{-- Current Waiter --}}
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Assigned Waiter') }}</label>
                                    <p class="text-sm font-semibold mt-0.5" :class="modalTable.waiter_name ? 'text-indigo-700' : 'text-gray-400'" x-text="modalTable.waiter_name || '{{ __('Unassigned') }}'"></p>
                                </div>

                                @if($isManager ?? false)
                                {{-- Assignment Form --}}
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
                            {{-- Modal Footer --}}
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
        </div>
    </div>

    <script>
        function tablesPage() {
            const tablesData = @js($tables->map(fn ($t) => [
                'id' => $t->id,
                'table_number' => $t->table_number,
                'capacity' => $t->capacity,
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'waiter_name' => $t->activeAssignment?->user?->name,
                'waiter_id' => $t->activeAssignment?->user_id,
                'shift_id' => $t->activeAssignment?->shift_id,
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

            return {
                tab: 'grid',
                modalOpen: false,
                modalTable: null,
                selectedShiftId: '',
                tables: [...tablesData],

                get selectedWaiterId() {
                    return this.selectedShiftId ? (shiftsMap[this.selectedShiftId] || '') : '';
                },

                statusStyle(status, prop) {
                    return (statusStyles[status] || statusStyles.available)[prop] || '';
                },

                openModal(tableId) {
                    this.modalTable = this.tables.find(t => t.id === tableId) || null;
                    this.selectedShiftId = this.modalTable?.shift_id || '';
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                init() {
                    if (window.Echo) {
                        window.Echo.private('tables')
                            .listen('.TableStatusUpdated', (e) => {
                                const idx = this.tables.findIndex(t => t.id === e.id);
                                if (idx !== -1) {
                                    this.tables[idx] = { ...this.tables[idx], ...e };
                                }
                                if (this.modalTable && this.modalTable.id === e.id) {
                                    this.modalTable = { ...this.modalTable, ...e };
                                }
                            });
                    }
                },
            };
        }
    </script>
</x-app-layout>
