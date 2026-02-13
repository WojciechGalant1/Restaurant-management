/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPortRaw = import.meta.env.VITE_REVERB_PORT || window.location.port;
const reverbPort = reverbPortRaw ? Number.parseInt(String(reverbPortRaw), 10) : undefined;
const forceTLS = (import.meta.env.VITE_REVERB_SCHEME ?? window.location.protocol.replace(':', '') ?? 'https') === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: reverbPort ?? (forceTLS ? 443 : 80),
    wssPort: reverbPort ?? (forceTLS ? 443 : 80),
    forceTLS,
    enabledTransports: ['ws', 'wss'],
});
