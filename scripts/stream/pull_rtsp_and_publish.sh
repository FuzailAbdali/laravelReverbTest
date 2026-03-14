#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 2 ]; then
  echo "Usage: $0 <camera_rtsp_url> <target_stream_name>"
  exit 1
fi

CAMERA_RTSP_URL="$1"
TARGET_STREAM_NAME="$2"

# Pull from camera and publish to local MediaMTX RTSP endpoint.
ffmpeg -re -rtsp_transport tcp -i "$CAMERA_RTSP_URL" \
  -c copy \
  -f rtsp "rtsp://127.0.0.1:8554/${TARGET_STREAM_NAME}"
