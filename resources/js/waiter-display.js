export default function waiterDisplay(config = {}) {
    return {
        items: config.items || [],
        readyStatus: config.readyStatus,
        servedStatus: config.servedStatus,
        cancelledStatus: config.cancelledStatus,
        voidedStatus: config.voidedStatus,
        markServedUrlTemplate: config.markServedUrlTemplate || '',

        init() {
            this.setupEcho();
        },

        async markServed(item) {
            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.content;
            if (!csrf) return;
            try {
                const res = await fetch(item.mark_served_url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({}),
                });
                if (!res.ok) throw new Error('Request failed');
                const idx = this.items.findIndex((i) => i.id === item.id);
                if (idx !== -1) this.items.splice(idx, 1);
            } catch (e) {
            }
        },

        setupEcho() {
            if (window.Echo) {
                this.connectEcho();
            } else {
                setTimeout(() => this.setupEcho(), 100);
            }
        },

        connectEcho() {
            try {
                const kitchenChannel = window.Echo.private('kitchen');
                kitchenChannel
                    .listen('.OrderItemStatusUpdated', (e) => {
                        const index = this.items.findIndex((i) => i.id === e.id);
                        if (e.status === this.readyStatus) {
                            if (index === -1) {
                                const markServedUrl = this.markServedUrlTemplate.replace(':id', e.id);
                                this.items.unshift({
                                    ...e,
                                    total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                    mark_served_url: markServedUrl,
                                });
                            } else {
                                this.items[index] = { ...this.items[index], ...e };
                            }
                        } else if (e.status === this.servedStatus || e.status === this.cancelledStatus || e.status === this.voidedStatus || e.status !== this.readyStatus) {
                            if (index !== -1) this.items.splice(index, 1);
                        }
                    })
                    .listen('.OrderItemCreated', (e) => {
                        if (e.status === this.readyStatus && !this.items.find((i) => i.id === e.id)) {
                            const markServedUrl = this.markServedUrlTemplate.replace(':id', e.id);
                            this.items.unshift({
                                ...e,
                                total_price: (parseFloat(e.quantity) * parseFloat(e.unit_price || 0)).toFixed(2),
                                mark_served_url: markServedUrl,
                            });
                        }
                    });

                // Listen for table status changes (e.g. host marks table as occupied)
                // and refresh the waiter view so "My Tables" reflects the new status.
                const tablesChannel = window.Echo.private('tables');
                tablesChannel.listen('.TableStatusUpdated', (e) => {
                    // Optional: only reload if this waiter cares about that table.
                    // For now, a full reload keeps logic simple and always in sync.
                    window.location.reload();
                });

                if (window.Alpine && window.Echo.connector?.pusher) {
                    if (!window.Alpine.store('echo')) {
                        window.Alpine.store('echo', { connected: false });
                    }
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        window.Alpine.store('echo').connected = true;
                    });
                    window.Echo.connector.pusher.connection.bind('disconnected', () => {
                        window.Alpine.store('echo').connected = false;
                    });
                    window.Alpine.store('echo').connected = true;
                }
            } catch (error) {
                setTimeout(() => this.setupEcho(), 1000);
            }
        },
    };
}

