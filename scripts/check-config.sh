#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

[ -f docker-compose.yml ] || { echo "docker-compose.yml missing"; exit 1; }
[ -f mediamtx/mediamtx.yml ] || { echo "mediamtx config missing"; exit 1; }

if command -v docker >/dev/null 2>&1; then
  docker compose config >/dev/null
  echo "docker compose config: OK"
else
  echo "docker not installed; skipped docker compose validation"
fi

echo "basic config checks: OK"
