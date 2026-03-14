import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbHost = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const appKey = import.meta.env.VITE_REVERB_APP_KEY ?? 'local-app-key';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: appKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

export function subscribeToScore(matchId, onUpdate) {
    window.Echo.channel('score.public')
        .listen('.score.updated', onUpdate);

    window.Echo.private(`score.match.${matchId}`)
        .listen('.score.updated', onUpdate);
}
