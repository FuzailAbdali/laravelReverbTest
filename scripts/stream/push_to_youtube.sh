#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 2 ]; then
  echo "Usage: $0 <stream_name_in_mediamtx> <youtube_stream_key> [fps] [video_bitrate]"
  exit 1
fi

STREAM_NAME="$1"
YOUTUBE_STREAM_KEY="$2"
FPS="${3:-30}"
VIDEO_BITRATE="${4:-4000k}"

INPUT_RTSP="rtsp://127.0.0.1:8554/${STREAM_NAME}"
YOUTUBE_RTMP="rtmp://a.rtmp.youtube.com/live2/${YOUTUBE_STREAM_KEY}"

# Transcode for reliable YouTube ingest.
ffmpeg -rtsp_transport tcp -i "$INPUT_RTSP" \
  -c:v libx264 -preset veryfast -maxrate "$VIDEO_BITRATE" -bufsize 2M -pix_fmt yuv420p -g $((FPS*2)) -r "$FPS" \
  -c:a aac -ar 44100 -b:a 128k \
  -f flv "$YOUTUBE_RTMP"
