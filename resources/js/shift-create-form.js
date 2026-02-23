export default function shiftCreateForm(config = {}) {
    return {
        availabilityUrl: config.availabilityUrl,
        coverageUrl: config.coverageUrl,
        usersByRole: config.usersByRole || {},
        hoursPerUser: config.hoursPerUser || {},
        maxHoursPerWeek: config.maxHoursPerWeek || 40,
        weekdayLabels: config.weekdayLabels || {},
        baseDate: config.initialDate || '',
        shiftType: config.initialShiftType || 'morning',
        startTime: config.initialStartTime || '',
        endTime: config.initialEndTime || '',
        notes: config.initialNotes || '',
        shiftTypeTimes: config.shiftTypeTimes || {},
        selectedUserIds: config.selectedUserIds || [],
        replicateDays: config.replicateDays || [],
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
                this.replicateDays.forEach((d) => {
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
            this.coverageDates.forEach((date) => {
                const c = this.coverage[date];
                if (!c) return;
                if (c.chef === 0 && this.weekdayLabels) {
                    msg.push(this.formatDate(date) + ': No chef assigned');
                }
            });
            return msg;
        },

        formatDate(iso) {
            const d = new Date(iso + 'T12:00:00');
            return d.toLocaleDateString(undefined, {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
            });
        },

        getUserName(uid) {
            const id = String(uid);
            for (const role of Object.values(this.usersByRole)) {
                for (const u of role) {
                    if (String(u.id) === id) {
                        return `${u.first_name || ''} ${u.last_name || ''}`.trim();
                    }
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
                const params = new URLSearchParams();
                params.set('date', this.baseDate);
                this.selectedUserIds.forEach((id) => params.append('user_ids[]', id));
                const url = `${this.availabilityUrl}?${params.toString()}`;
                const r = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await r.json();
                this.availability = r.ok && data && typeof data === 'object' && !data.message ? data : {};
            } catch (e) {
                console.error(e);
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
                const url = `${this.coverageUrl}?dates=${this.coverageDates.join(',')}`;
                const r = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await r.json();
                this.coverage = r.ok && data && typeof data === 'object' && !data.message ? data : {};
            } catch (e) {
                console.error(e);
                this.coverage = {};
            }
            this.coverageLoading = false;
        },

        init() {
            this.$watch('baseDate', () => {
                this.fetchAvailability();
                this.fetchCoverage();
            });
            this.$watch('replicateDays', () => this.fetchCoverage(), { deep: true });
            this.$watch('selectedUserIds', () => this.fetchAvailability(), { deep: true });
            this.fetchCoverage();
            this.fetchAvailability();
        },
    };
}

