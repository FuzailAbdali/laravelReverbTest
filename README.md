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
