#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <public-ip-or-domain>"
  exit 1
fi

HOST="$1"
PORTS=(8080 8081 8554 1935 8888 8889 9997)

echo "Checking TCP reachability for ${HOST}..."
for port in "${PORTS[@]}"; do
  if timeout 2 bash -lc "</dev/tcp/${HOST}/${port}" 2>/dev/null; then
    echo "[OK] ${HOST}:${port} reachable"
  else
    echo "[WARN] ${HOST}:${port} unreachable"
  fi
done
