# Laravel Reverb + MediaMTX Realtime Streaming Blueprint

This repository now contains a **production-ready blueprint** for:

- Public and private realtime channels with Laravel Reverb.
- Score management events (`score.updated`) synced to all connected clients.
- IP camera ingestion via two approaches:
  1. Camera pushes stream to platform (RTSP/RTMP ingest).
  2. Server pulls stream from camera URL (local or remote through port forwarding).
- Recording streams.
- RTMP egress to YouTube.
- Embedded live stream page for your web platform.
- Horizontal scaling strategy for Reverb and MediaMTX.

---

## 1) Architecture

```text
IP Camera(s)
   |  (RTSP/RTMP push) OR (server pull)
   v
MediaMTX (ingest, transcode pass-through, record, relay)
   |--> RTSP/HLS/WebRTC for local web playback
   |--> RTMP publish to YouTube

Laravel App + Reverb + Redis
   |--> public channel: score.public
   |--> private channel: score.match.{matchId}
   |--> browser clients subscribe with Laravel Echo
```

---

## 2) Included files

- `docker-compose.yml` – Base stack for app, Redis, Reverb, and MediaMTX.
- `config/mediamtx.yml` – Media server config including recording and stream paths.
- `routes/channels.php` – Public/private channel authorization examples.
- `app/Events/ScoreUpdated.php` – Broadcast event used by realtime score updates.
- `app/Http/Controllers/ScoreController.php` – Example endpoint to emit updates.
- `resources/js/bootstrap-echo.js` – Echo + Reverb + subscription setup.
- `resources/views/live.blade.php` – Embedded web playback + score panel.
- `scripts/port-forward-test.sh` – Connectivity validation script.
- `scripts/start-youtube-restream.sh` – FFmpeg script to push to YouTube RTMP.

---

## 3) Reverb channels

### Public channel
- Channel name: `score.public`
- Any connected client can subscribe.

### Private channel
- Channel name: `score.match.{matchId}`
- Authorized only if user is assigned to the match (see `routes/channels.php`).

---

## 4) IP camera ingestion approaches

### Approach A: Camera pushes to your platform
Configure camera stream destination to MediaMTX:

- RTSP push: `rtsp://<server-ip>:8554/cam1`
- RTMP push: `rtmp://<server-ip>:1935/cam1`

### Approach B: Server pulls camera URL
In `config/mediamtx.yml`, set source path:

```yaml
paths:
  cam1:
    source: rtsp://user:pass@CAMERA_IP:554/stream1
```

Use this when camera is remote and you already have port forwarding/VPN in place.

---

## 5) Recording

Recording is enabled in `config/mediamtx.yml` with segmented MP4 output under `./recordings`.

---

## 6) YouTube RTMP push

1. Obtain your YouTube stream key.
2. Run:

```bash
YOUTUBE_STREAM_KEY=xxxx scripts/start-youtube-restream.sh cam1
```

This publishes `cam1` from MediaMTX to YouTube ingest.

---

## 7) Embed live stream on your platform

`resources/views/live.blade.php` includes:
- native HLS `<video>` playback (`.m3u8`),
- fallback display instructions,
- realtime score section updated from Reverb events.

For YouTube embed, use:

```html
<iframe
  width="100%"
  height="480"
  src="https://www.youtube.com/embed/live_stream?channel=YOUR_CHANNEL_ID"
  title="YouTube Live"
  frameborder="0"
  allow="autoplay; encrypted-media; picture-in-picture"
  allowfullscreen>
</iframe>
```

---

## 8) Port forwarding test

Use:

```bash
scripts/port-forward-test.sh <public-host-or-ip>
```

Checks:
- MediaMTX RTSP/RTMP/HLS ports (`8554`, `1935`, `8888`)
- Reverb websocket port (`8080`)

---

## 9) Scaling notes

- **Reverb**: run multiple instances behind L4/L7 LB, with Redis pub/sub shared backend.
- **MediaMTX**:
  - Vertical scale for high bitrate workloads.
  - Horizontal split by camera groups or geo region.
  - Use dedicated edge nodes for ingest; central nodes for relay and recording.
- Persist recordings to object storage via post-processing jobs.

---

## 10) What to do next in a real Laravel app

Because this repository started empty, these files are a drop-in blueprint.
If you already have an existing Laravel codebase, copy these files/sections into your app and wire environment variables from `.env`.

## 11) Alternative media server fallback

If MediaMTX is not suitable for a specific camera/vendor scenario, use one of:

- **SRS (Simple Realtime Server)** for robust RTMP-centric flows.
- **Nginx + nginx-rtmp module** for minimal RTMP relay setups.
- **OvenMediaEngine** when low-latency WebRTC delivery is required.

Keep the same Laravel/Reverb score event system regardless of selected media server.
