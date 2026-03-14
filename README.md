# Laravel Reverb + MediaMTX Realtime Streaming Blueprint

This repository now contains an implementation blueprint for:

- Laravel Reverb **public/private channels**
- Realtime **score management** event broadcasting to all clients
- IP camera ingest using **two approaches**
- Stream transport via **RTSP / RTMP / HLS / WebRTC** (MediaMTX)
- **Recording** streams on server
- Generating/pushing **RTMP to YouTube**
- Camera management input/API for adding streams
- Local and remote access via **port forwarding**
- Initial **scaling strategy** for websocket + media services

---

## 1) Architecture (high-level)

- **Laravel app**
  - Score APIs + camera APIs
  - Broadcast events via Reverb
  - Authorizes private channels
- **Laravel Reverb**
  - Handles websocket realtime fanout
- **Redis**
  - Pub/sub + queue + cache backend for scalable broadcasting
- **MediaMTX**
  - Ingest RTSP/RTMP/SRT/WebRTC
  - Restream to RTSP/RTMP/HLS/WebRTC
  - Record streams to disk
  - Pull camera streams (server-pull mode)
  - Accept camera pushes (camera-push mode)
- **Nginx (optional gateway)**
  - Reverse proxy for app/reverb/media endpoints

---

## 2) Streaming ingest approaches for IP cameras

### Approach A: Camera Pushes to Platform
Camera sends stream directly to MediaMTX.

Examples:
- RTSP publish: `rtsp://<server>:8554/camera/front_gate`
- RTMP publish: `rtmp://<server>:1935/camera/front_gate`

Use this when camera supports publishing and has connectivity to your server.

### Approach B: Server Pulls from Camera
MediaMTX fetches camera URL:
- `source: rtsp://user:pass@<camera-ip>:554/stream1`

Use this when platform can access camera IP (same LAN/VPN/port-forwarded).

---

## 3) Realtime score synchronization with Reverb

Flow:
1. Any client updates score (HTTP API)
2. Laravel stores score and emits `ScoreUpdated`
3. Reverb broadcasts to channel
4. All clients (including viewing clients) receive update instantly

Channels:
- Public: `match.{matchId}`
- Private: `private-match.{matchId}` (authorized users only)

---

## 4) Quick start

> This repository is a scaffold. Create Laravel app files from stubs and wire into your actual app.

### 4.1 Environment

Copy `.env.example` values shown in [`deploy/env.example`](deploy/env.example) into your Laravel `.env`.

### 4.2 Run services

```bash
docker compose up -d --build
```

### 4.3 Laravel setup inside app container

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 4.4 Start websocket server

```bash
docker compose up -d reverb
```

---

## 5) Port forwarding checklist

Forward these ports from router/firewall to server:

- `8080` -> app web UI
- `8081` -> Reverb websockets
- `8554` -> RTSP ingest/egress
- `1935` -> RTMP ingest/egress
- `8888` -> HLS playback
- `8889` -> WebRTC HTTP signaling

Then test from remote network:

```bash
bash scripts/port-forward-check.sh <public-ip-or-domain>
```

---

## 6) YouTube push and embedded live page

### Push to YouTube

Use ffmpeg relay (replace placeholders):

```bash
ffmpeg -re -i rtsp://localhost:8554/camera/front_gate \
  -c:v libx264 -preset veryfast -b:v 3500k \
  -c:a aac -ar 44100 -b:a 128k \
  -f flv "rtmp://a.rtmp.youtube.com/live2/<YOUTUBE_STREAM_KEY>"
```

### Embed in your platform page

Use YouTube iframe on a Laravel blade page; see `stubs/resources/views/live-embed.blade.php`.

---

## 7) Recording

MediaMTX is configured to record to `/recordings/%path/%Y-%m-%d_%H-%M-%S`.

For each path you can enable/disable recording in `mediamtx/mediamtx.yml`.

---

## 8) Scaling notes

- Scale Laravel app horizontally behind load balancer
- Run Reverb workers separately and scale replicas
- Use shared Redis for pub/sub
- Put MediaMTX in dedicated nodes if camera count grows
- Prefer regional edge ingest where cameras are geographically distributed
- Use object storage lifecycle for recordings

---

## 9) Files in this repo

- `docker-compose.yml` - service stack
- `docker/Dockerfile` - PHP/Laravel runtime
- `docker/nginx/default.conf` - app + websocket reverse proxy
- `mediamtx/mediamtx.yml` - ingest/restream/record config
- `stubs/...` - Laravel code samples for channels/events/controllers/views
- `scripts/port-forward-check.sh` - connectivity smoke check
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

