#!/usr/bin/env bash
set -euo pipefail

PATH_NAME="${1:-cam1}"
STREAM_KEY="${YOUTUBE_STREAM_KEY:-}"
INGEST_URL="${YOUTUBE_INGEST_URL:-rtmp://a.rtmp.youtube.com/live2}"
INPUT_URL="${INPUT_URL:-rtsp://localhost:8554/${PATH_NAME}}"

if [[ -z "$STREAM_KEY" ]]; then
  echo "Set YOUTUBE_STREAM_KEY env var first."
  exit 1
fi

exec ffmpeg -re -i "$INPUT_URL" \
  -c:v copy -c:a aac -ar 44100 -b:a 128k \
  -f flv "${INGEST_URL}/${STREAM_KEY}"
