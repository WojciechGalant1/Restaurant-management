export default function kitchenDisplay(config = {}) {
    return {
        items: config.items || [],
        servedStatus: config.servedStatus,
        cancelledStatus: config.cancelledStatus,
        voidedStatus: config.voidedStatus,
        justNowLabel: config.justNowLabel || 'just now',

        init() {
            this.setupEcho();
        },

        async updateStatus(item, newStatus) {
            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.content;
            if (!csrf) return;
            try {
                const res = await fetch(item.update_url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ status: newStatus }),
                });
                if (!res.ok) throw new Error('Request failed');
                const idx = this.items.findIndex((i) => i.id === item.id);
                if (idx !== -1) {
                    this.items[idx] = {
                        ...this.items[idx],
                        status: newStatus,
                        updated_at_human: this.justNowLabel,
                    };
                }
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
                const channel = window.Echo.private('kitchen');

                channel
                    .listen('.OrderItemCreated', (e) => {
                        if (!this.items.find((i) => i.id === e.id)) {
                            this.items.unshift(e);
                        }
                    })
                    .listen('.OrderItemStatusUpdated', (e) => {
                        const index = this.items.findIndex((i) => i.id === e.id);
                        if (index !== -1) {
                            if (e.status === this.servedStatus || e.status === this.cancelledStatus || e.status === this.voidedStatus) {
                                this.items = this.items.filter((i) => i.id !== e.id);
                            } else {
                                this.items[index] = { ...this.items[index], ...e };
                            }
                        } else if (e.status !== this.servedStatus && e.status !== this.cancelledStatus && e.status !== this.voidedStatus) {
                            this.items.unshift(e);
                        }
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
                if (window.Alpine && window.Alpine.store('echo')) {
                    window.Alpine.store('echo').connected = false;
                }
                setTimeout(() => this.setupEcho(), 1000);
            }
        },
    };
}

