export default function tablesPage(page = {}) {
    const allTablesData = page.allTables || [];
    const roomsData = page.rooms || [];
    const shiftsMap = page.shiftsMap || {};

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
        cleaning: {
            cardBg: 'bg-orange-50 border-orange-300 hover:border-orange-500',
            dot: 'bg-orange-500',
            text: 'text-orange-700',
        },
        reserved: {
            cardBg: 'bg-amber-50 border-amber-300 hover:border-amber-500',
            dot: 'bg-amber-500',
            text: 'text-amber-700',
        },
    };

    const isManager = !!page.isManager;
    const isHost = !!page.isHost;
    const reorderUrl = page.reorderUrl;
    const confirmDeleteRoom = page.confirmDeleteRoom || 'Tables in this room will become unassigned. Continue?';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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

        roomHasNoWaiter(roomId) {
            const tables = this.tablesInRoom(roomId);
            return tables.length > 0 && tables.every(t => !t.waiter_name);
        },

        waiterCountInRoom(roomId) {
            const tables = this.tablesInRoom(roomId);
            const ids = new Set(tables.filter(t => t.waiter_id).map(t => t.waiter_id));
            return ids.size;
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
                    if (!res.ok) {
                        const d = await res.json();
                        this.roomError = d.message || 'Error';
                        return;
                    }
                    const updated = await res.json();
                    const idx = this.rooms.findIndex(r => r.id === updated.id);
                    if (idx !== -1) this.rooms[idx] = { ...this.rooms[idx], ...updated };
                    this.allTables.forEach(t => {
                        if (t.room_id === updated.id) {
                            t.room_name = updated.name;
                            t.room_color = updated.color;
                        }
                    });
                } else {
                    const res = await fetch('/rooms', { method: 'POST', headers, body: JSON.stringify(this.roomForm) });
                    if (!res.ok) {
                        const d = await res.json();
                        this.roomError = d.message || 'Error';
                        return;
                    }
                    const created = await res.json();
                    this.rooms.push({
                        id: created.id,
                        name: created.name,
                        description: created.description,
                        color: created.color,
                        sort_order: created.sort_order,
                    });
                    this.$nextTick(() => {
                        if (window.reinitTableSortables) window.reinitTableSortables(this);
                    });
                }
                this.closeRoomModal();
            } catch (e) {
                this.roomError = e.message;
            }
        },

        async deleteRoom(room) {
            if (!confirm(confirmDeleteRoom)) return;
            const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };
            await fetch(`/rooms/${room.id}`, { method: 'DELETE', headers });
            this.rooms = this.rooms.filter(r => r.id !== room.id);
            this.allTables.forEach(t => {
                if (t.room_id === room.id) {
                    t.room_id = null;
                    t.room_name = null;
                    t.room_color = null;
                }
            });
        },

        applyTableMove({ fromRoomId, toRoomId, tableId, oldIndex, newIndex }) {
            const parseId = (id) => (id === 'unassigned' ? null : parseInt(id, 10));
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
                    if (r) {
                        moved.room_name = r.name;
                        moved.room_color = r.color;
                    }
                } else {
                    moved.room_name = null;
                    moved.room_color = null;
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
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
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

