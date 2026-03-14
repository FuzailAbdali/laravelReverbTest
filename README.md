# Laravel Reverb Testbed: Score + Streaming Foundation

This repository provides a starting implementation for:

- Reverb public + private channels for real-time score updates.
- Score management API that broadcasts updates to all connected clients.
- Camera input management API.
- MediaMTX setup for RTSP/RTMP ingest, HLS playback, recording, and YouTube relay.

## Implemented Files

- Broadcasting auth: `routes/channels.php`
- Realtime event: `app/Events/ScoreUpdated.php`
- Score update API: `app/Http/Controllers/ScoreController.php`
- Camera CRUD entrypoint (input camera details): `app/Http/Controllers/CameraSourceController.php`
- DB models + migrations:
  - `app/Models/MatchScore.php`
  - `app/Models/CameraSource.php`
  - `database/migrations/2026_01_01_000000_create_match_scores_table.php`
  - `database/migrations/2026_01_01_000001_create_camera_sources_table.php`
- Streaming stack:
  - `docker-compose.yml`
  - `docker/mediamtx.yml`
- Stream helper scripts:
  - `scripts/stream/pull_rtsp_and_publish.sh`
  - `scripts/stream/push_to_youtube.sh`

## Quick Start

```bash
docker compose up -d redis mediamtx
```

### Camera ingest mode 1 (camera pushes to platform)

- RTSP publish: `rtsp://YOUR_HOST:8554/<stream_key>`
- RTMP publish: `rtmp://YOUR_HOST:1935/<stream_key>`

### Camera ingest mode 2 (platform pulls camera RTSP)

```bash
./scripts/stream/pull_rtsp_and_publish.sh rtsp://user:pass@camera-ip:554/stream1 cam01
```

### Playback on web platform

- HLS URL: `http://YOUR_HOST:8888/<stream_key>/index.m3u8`
- YouTube embed URL: `https://www.youtube.com/embed/live_stream?channel=YOUR_CHANNEL_ID`

### Push stream to YouTube

```bash
./scripts/stream/push_to_youtube.sh cam01 YOUR_YOUTUBE_STREAM_KEY
```

## Port Forwarding

Open/forward:

- `8554/tcp` RTSP
- `1935/tcp` RTMP
- `8888/tcp` HLS playback
- `8080/tcp` Reverb websocket
- `8000/tcp` App HTTP

## Scaling

- Scale Laravel app and Reverb workers horizontally.
- Keep Redis shared across instances.
- Keep MediaMTX as a dedicated media service and scale by grouping cameras per site/region.

See `docs/reverb-streaming-architecture.md` for the detailed flow.
# Laravel Reverb + MediaMTX Realtime Score & Streaming Starter

This repository contains a practical starter architecture for a realtime scoreboard + IP camera streaming platform using:

- **Laravel + Reverb** for realtime public/private channel events.
- **Redis** as the queue/cache/broadcast backend.
- **MediaMTX** as the free media server for RTSP/RTMP/HLS/WebRTC ingest/distribution and recording.
- **FFmpeg hooks** for forwarding an RTMP stream to YouTube.

## What is included

1. **Public & private channel design** for score updates.
2. **Score update event flow** where a score update in one client broadcasts to all clients in real-time.
3. **IP camera support**:
   - Camera push mode (camera pushes RTSP/RTMP to your server).
   - Server pull mode (MediaMTX pulls from camera RTSP URL).
4. **Recording** via MediaMTX with path-based recordings.
5. **YouTube RTMP relay** flow.
6. **Port-forwarding testing guidance**.
7. **Scalability guidance** for Reverb + queue workers + MediaMTX.

---

## Quick start

### 1) Bring up services

```bash
docker compose up -d
```

This starts:

- `redis` on `6379`
- `mediamtx` on RTSP `8554`, RTMP `1935`, HLS `8888`, WebRTC `8889`

### 2) Copy MediaMTX environment

```bash
cp .env.example .env
```

Then edit:

- `YOUTUBE_STREAM_KEY`
- `YOUTUBE_RTMP_URL`

### 3) Validate MediaMTX config

```bash
./scripts/check-config.sh
```

### 4) Test local stream ingest (sample)

Publish a test pattern:

```bash
ffmpeg -re -f lavfi -i testsrc=size=1280x720:rate=30 \
  -f lavfi -i sine=frequency=1000:sample_rate=44100 \
  -c:v libx264 -preset veryfast -tune zerolatency \
  -c:a aac -f rtsp rtsp://localhost:8554/camera_push/testcam
```

Play via HLS:

- `http://localhost:8888/camera_push/testcam/index.m3u8`

---

## Laravel integration notes

Use the files under `stubs/laravel/` as drop-in references inside your Laravel app.

Key pieces:

- `routes/channels.php` for private channel auth.
- `app/Events/ScoreUpdated.php` broadcast event.
- `app/Http/Controllers/ScoreController.php` endpoint that updates score and emits event.
- `resources/js/echo-score.js` Echo client listeners.
- Migrations for `scores` and `cameras`.

---

## Two camera ingest approaches

### A) Camera push (recommended for NAT/unreliable upstream)

Camera sends stream to your public endpoint:

- RTSP: `rtsp://YOUR_SERVER_IP:8554/camera_push/{cameraId}`
- RTMP: `rtmp://YOUR_SERVER_IP:1935/camera_push/{cameraId}`

### B) Server pull

Platform stores camera RTSP URL and MediaMTX pulls using configured source path.
Useful when camera is reachable from server via LAN/VPN/port-forwarding.

---

## Port forwarding checklist

Forward from router/firewall to MediaMTX host:

- TCP/UDP `8554` (RTSP)
- TCP `1935` (RTMP)
- TCP `8888` (HLS)
- TCP/UDP `8889` (WebRTC)

For Laravel/Reverb frontend API:

- App HTTP(S) port (`80/443`)
- Reverb websocket port (as configured)

---

## Scale notes

- Run **multiple Laravel app instances** behind load balancer.
- Use centralized **Redis** and queue workers.
- Sticky sessions are usually unnecessary if auth tokens are stateless.
- Use **multiple MediaMTX instances** by camera regions/groups.
- Use object storage for recordings if local disk is insufficient.

