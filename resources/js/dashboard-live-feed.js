export default function dashboardLiveFeed() {
    return {
        feedItems: [],
        maxItems: 50,
        feedId: 0,

        relativeTime(ts) {
            if (!ts) return '–';
            const sec = Math.floor((Date.now() - ts) / 1000);
            if (sec < 60) return 'just now';
            if (sec < 3600) return Math.floor(sec / 60) + ' min ago';
            if (sec < 86400) return Math.floor(sec / 3600) + ' h ago';
            return Math.floor(sec / 86400) + ' d ago';
        },

        init() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(() => this.init(), 200);
                return;
            }

            // Force Alpine reactivity for relative times
            setInterval(() => {
                this.feedItems = this.feedItems.slice();
            }, 60000);

            try {
                const channel = window.Echo.private('dashboard');
                const push = (e) => {
                    const msg = e.feed_message || e.message;
                    const link = e.feed_link || e.link || null;
                    if (msg) {
                        this.feedItems.unshift({
                            id: ++this.feedId,
                            feed_message: msg,
                            feed_link: link,
                            added_at: Date.now(),
                        });
                        if (this.feedItems.length > this.maxItems) {
                            this.feedItems = this.feedItems.slice(0, this.maxItems);
                        }
                    }
                };

                channel
                    .listen('.OrderCreated', push)
                    .listen('.OrderItemStatusUpdated', push)
                    .listen('.ReservationCreated', push)
                    .listen('.ReservationUpdated', push)
                    .listen('.InvoiceIssued', push);

                // Optional: connection state store
                if (window.Alpine && window.Echo.connector && window.Echo.connector.pusher) {
                    if (!window.Alpine.store('echo')) {
                        window.Alpine.store('echo', { connected: false });
                    }
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        window.Alpine.store('echo').connected = true;
                    });
                    window.Alpine.store('echo').connected = true;
                }
            } catch (err) {
                console.error('Dashboard Live Feed:', err);
            }
        },
    };
}

