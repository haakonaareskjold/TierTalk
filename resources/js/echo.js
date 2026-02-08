import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Use current browser hostname for dynamic Cloudflare Tunnel URLs
const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const isSecure = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: isSecure ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: 443,
    forceTLS: isSecure,
    enabledTransports: ['ws', 'wss'],
});
