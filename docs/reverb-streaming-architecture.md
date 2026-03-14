# Laravel Reverb + Score + IP Camera Streaming Architecture

This repository now includes a practical foundation for:

1. **Public and private Reverb channels** for score updates.
2. **Score management APIs** that broadcast updates to all connected clients.
3. **IP camera ingest** using either:
   - camera push mode (camera pushes stream to your MediaMTX endpoint),
   - server pull mode (server pulls RTSP stream from camera).
4. **Recording support** and **RTMP push to YouTube**.
5. **Port-forwarding ready setup** via Docker compose and MediaMTX listener ports.
6. **Scalability baseline** using Redis and horizontal Laravel/Reverb scaling.

## 1) Reverb Channels

- Public channel: `scoreboard.{matchId}`
- Private channel: `private-match.{matchId}`

Private authorization is implemented in `routes/channels.php`.

## 2) Real-time Score Update Flow

1. Client calls API endpoint to update score.
2. `ScoreController` persists score snapshot.
3. `ScoreUpdated` event broadcasts to public + private channels.
4. All clients subscribed to those channels receive updates in real-time.

## 3) IP Camera Ingest Approaches

### A. Camera Push to Platform

Configure camera RTSP/RTMP target to push into MediaMTX:
- RTSP target example: `rtsp://YOUR_PUBLIC_HOST:8554/cam01`
- RTMP target example: `rtmp://YOUR_PUBLIC_HOST:1935/cam01`

### B. Server Pull from Camera

Use ffmpeg script to pull from camera and publish to local MediaMTX:

```bash
./scripts/stream/pull_rtsp_and_publish.sh rtsp://user:pass@camera-ip:554/stream1 cam01
```

## 4) Recording

MediaMTX recording is enabled in `docker/mediamtx.yml` via:
- `record: yes`
- `recordPath: /recordings/%path/%Y-%m-%d_%H-%M-%S`

## 5) YouTube RTMP Push

Use script:

```bash
./scripts/stream/push_to_youtube.sh cam01 YOUR_YOUTUBE_STREAM_KEY
```

This relays the internal stream to YouTube ingest.

## 6) Embedded Playback

Web app should embed one of:
- YouTube Live embed URL for global audiences.
- HLS playback URL from MediaMTX (`http://host:8888/cam01/index.m3u8`) for internal platform playback.

## 7) Port Forwarding Checklist

Forward these ports from your router/cloud firewall to the MediaMTX host:

- `8554/tcp` RTSP
- `1935/tcp` RTMP
- `8888/tcp` HLS / low-latency web playback endpoint

For Reverb/Laravel app:

- `8080/tcp` Reverb
- `8000/tcp` Laravel app (or your chosen web server port)

## 8) Scaling Notes

- Run multiple Laravel app instances behind a load balancer.
- Use shared Redis for queue/cache and broadcast internals.
- Keep MediaMTX as dedicated media plane service.
- For high camera counts, split media services by region/site and aggregate playback metadata centrally.

## 9) Security

- Use signed/private streams where applicable.
- Restrict private channels by team/match membership.
- Never expose raw camera credentials in frontend code.
- Lock inbound IP allowlists for camera ingest whenever possible.
