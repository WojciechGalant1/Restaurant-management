export default function reservationCreateForm(config = {}) {
    const availableTablesUrl = config.availableTablesUrl || '/api/reservations/available-tables';
    const customerByPhoneUrl = config.customerByPhoneUrl || '/api/reservations/customer-by-phone';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    return {
        availableTablesUrl,
        customerByPhoneUrl,
        csrfToken,

        partySize: config.partySize || 2,
        reservationDate: config.reservationDate || '',
        reservationTime: config.reservationTime || '19:00',
        durationMinutes: config.durationMinutes || 120,
        customerName: config.customerName || '',
        phoneNumber: config.phoneNumber || '',
        notes: config.notes || '',
        selectedTags: config.selectedTags || [],

        selectedTableId: config.selectedTableId || null,
        availableTables: [],
        rooms: [],
        loading: false,
        fetchDebounce: null,
        roomFilter: null,

        durationOptions: [
            { value: 45, label: '45 min' },
            { value: 60, label: '1 h' },
            { value: 90, label: '1.5 h' },
            { value: 120, label: '2 h' },
            { value: 180, label: '3 h' },
        ],

        tagOptions: [
            { id: 'allergy', label: 'Allergy' },
            { id: 'birthday', label: 'Birthday' },
            { id: 'anniversary', label: 'Anniversary' },
            { id: 'vip', label: 'VIP' },
            { id: 'stroller', label: 'Stroller' },
        ],

        init() {
            const today = new Date().toISOString().slice(0, 10);
            if (!this.reservationDate) this.reservationDate = today;
            this.fetchAvailableTables();
        },

        get filteredRooms() {
            if (!this.roomFilter) return this.rooms;
            return this.rooms.filter((r) => {
                const name = (r.room_name || '').toLowerCase();
                return name.includes(this.roomFilter.toLowerCase());
            });
        },

        get selectedTable() {
            if (!this.selectedTableId) return null;
            return this.availableTables.find((t) => t.id === this.selectedTableId);
        },

        get canFetch() {
            return (
                this.partySize >= 1 &&
                this.reservationDate &&
                this.reservationTime &&
                this.durationMinutes >= 15
            );
        },

        get notesWithTags() {
            const tags = this.selectedTags
                .map((id) => {
                    const t = this.tagOptions.find((o) => o.id === id);
                    return t ? `[${t.label}]` : '';
                })
                .filter(Boolean);
            return tags.length ? tags.join(' ') + (this.notes ? ' ' + this.notes : '') : this.notes;
        },

        toggleTag(id) {
            const idx = this.selectedTags.indexOf(id);
            if (idx >= 0) {
                this.selectedTags.splice(idx, 1);
            } else {
                this.selectedTags.push(id);
            }
        },

        fetchAvailableTables() {
            if (!this.canFetch) {
                this.availableTables = [];
                this.rooms = [];
                return;
            }
            if (this.fetchDebounce) clearTimeout(this.fetchDebounce);
            this.fetchDebounce = setTimeout(() => this._doFetch(), 300);
        },

        async _doFetch() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    date: this.reservationDate,
                    time: this.reservationTime,
                    party_size: this.partySize,
                    duration: this.durationMinutes,
                });
                const res = await fetch(`${this.availableTablesUrl}?${params}`);
                const data = await res.json();
                this.availableTables = data.available || [];
                this.rooms = data.rooms || [];
                if (this.selectedTableId && !this.availableTables.find((t) => t.id === this.selectedTableId)) {
                    this.selectedTableId = null;
                }
            } catch (e) {
                this.availableTables = [];
                this.rooms = [];
            } finally {
                this.loading = false;
            }
        },

        async lookupCustomer() {
            const phone = (this.phoneNumber || '').trim();
            if (phone.length < 3) return;
            try {
                const res = await fetch(`${this.customerByPhoneUrl}?phone=${encodeURIComponent(phone)}`);
                const data = await res.json();
                if (data.customer_name && !this.customerName) {
                    this.customerName = data.customer_name;
                }
            } catch (_) {}
        },

        selectTable(tableId) {
            this.selectedTableId = this.selectedTableId === tableId ? null : tableId;
        },

        autoAssign() {
            const withoutConflict = this.availableTables.filter((t) => !t.has_conflict_risk);
            const best = withoutConflict.length ? withoutConflict[0] : this.availableTables[0];
            if (best) {
                this.selectedTableId = best.id;
            }
        },
    };
}
