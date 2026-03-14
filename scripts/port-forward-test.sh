#!/usr/bin/env bash
set -euo pipefail

TARGET_HOST="${1:-}"

if [[ -z "$TARGET_HOST" ]]; then
  echo "Usage: $0 <public-host-or-ip>"
  exit 1
fi

check_port() {
  local host="$1"
  local port="$2"
  local name="$3"

  if timeout 2 bash -c "</dev/tcp/${host}/${port}" 2>/dev/null; then
    echo "[OK] ${name} reachable at ${host}:${port}"
  else
    echo "[FAIL] ${name} not reachable at ${host}:${port}"
  fi
}

check_port "$TARGET_HOST" 8554 "MediaMTX RTSP"
check_port "$TARGET_HOST" 1935 "MediaMTX RTMP"
check_port "$TARGET_HOST" 8888 "MediaMTX HLS/API"
check_port "$TARGET_HOST" 8080 "Laravel Reverb WebSocket"
