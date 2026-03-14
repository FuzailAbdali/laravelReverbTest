# Architecture & Flow

## Realtime score management

1. Client submits score update (`POST /matches/{match}/score`).
2. Laravel validates and persists score.
3. Laravel emits `ScoreUpdated` event on:
   - Public channel `scores.public`
   - Private channel `scores.match.{matchId}`
4. All subscribed clients receive update via Reverb websocket.

## Camera management (UI/API idea)

Fields for camera input form:

- Camera Name
- Mode (`push` or `pull`)
- Stream key (for push)
- RTSP URL (for pull)
- Active flag

### Push mode

Camera encodes stream and pushes to MediaMTX:

- RTSP: `rtsp://host:8554/camera_push/<stream_key>`
- RTMP: `rtmp://host:1935/camera_push/<stream_key>`

### Pull mode

Store RTSP URL in DB and generate dynamic MediaMTX path to pull source.

## YouTube restream

- Publish stream to `youtube_relay/<stream_key>` path.
- MediaMTX triggers `runOnReady` FFmpeg process.
- FFmpeg forwards to: `rtmp://a.rtmp.youtube.com/live2/<key>`.

## Embedded live page

For web platform display, use HLS player (hls.js/video.js) or iframe for YouTube page.

Example HLS URL:

- `http://your-domain:8888/camera_push/<stream_key>/index.m3u8`

Example YouTube embed:

```html
<iframe
  width="560"
  height="315"
  src="https://www.youtube.com/embed/live_stream?channel=YOUR_CHANNEL_ID"
  title="YouTube live"
  frameborder="0"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
  allowfullscreen>
</iframe>
```

## Scaling options

- **App tier**: horizontally scale Laravel + Reverb nodes.
- **Event tier**: Redis pub/sub + queues for fan-out.
- **Media tier**:
  - Single MediaMTX for pilot.
  - Multi-region MediaMTX for production.
  - Place ingest near camera networks.
  - Use CDN for HLS playback.

