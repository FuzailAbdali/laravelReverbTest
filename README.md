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

