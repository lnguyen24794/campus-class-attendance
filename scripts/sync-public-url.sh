#!/usr/bin/env bash
#
# Gắn PUBLIC_APP_URL để điện thoại quét QR được.
#
# Usage:
#   ./scripts/sync-public-url.sh              # LAN IP (cùng Wi‑Fi)
#   ./scripts/sync-public-url.sh --tunnel     # Cloudflare quick tunnel (internet)
#   ./scripts/sync-public-url.sh --show       # chỉ in URL hiện tại
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

MODE="lan"
SHOW_ONLY=0

for arg in "$@"; do
  case "$arg" in
    --tunnel) MODE="tunnel" ;;
    --lan) MODE="lan" ;;
    --show) SHOW_ONLY=1 ;;
    -h|--help)
      sed -n '2,12p' "$0"
      exit 0
      ;;
    *)
      echo "Unknown option: $arg" >&2
      exit 1
      ;;
  esac
done

log() { printf '\n\033[1;36m==>\033[0m %s\n' "$*"; }
ok()  { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn(){ printf '\033[1;33m!\033[0m %s\n' "$*"; }

APP_PORT="${APP_PORT:-8000}"
if [[ -f .env ]]; then
  # shellcheck disable=SC1091
  set -a
  source <(grep -E '^(APP_PORT|PUBLIC_APP_URL)=' .env | sed 's/\r$//' || true)
  set +a
  APP_PORT="${APP_PORT:-8000}"
fi

detect_lan_ip() {
  local ip=""
  if command -v ipconfig >/dev/null 2>&1; then
    ip="$(ipconfig getifaddr en0 2>/dev/null || true)"
    [[ -z "$ip" ]] && ip="$(ipconfig getifaddr en1 2>/dev/null || true)"
  fi
  if [[ -z "$ip" ]] && command -v hostname >/dev/null 2>&1; then
    ip="$(hostname -I 2>/dev/null | awk '{print $1}' || true)"
  fi
  if [[ -z "$ip" ]] && command -v ip >/dev/null 2>&1; then
    ip="$(ip route get 1.1.1.1 2>/dev/null | awk '{for(i=1;i<=NF;i++) if($i=="src"){print $(i+1); exit}}' || true)"
  fi
  printf '%s' "$ip"
}

set_env_var() {
  local key="$1" value="$2"
  if grep -q "^${key}=" .env 2>/dev/null; then
    # Escape & for sed
    local escaped
    escaped="$(printf '%s' "$value" | sed 's/[&|]/\\&/g')"
    sed -i.bak "s|^${key}=.*|${key}=${escaped}|" .env
    rm -f .env.bak
  else
    printf '\n%s=%s\n' "$key" "$value" >> .env
  fi
}

current_public_url() {
  grep -E '^PUBLIC_APP_URL=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '\r' || true
}

if [[ "$SHOW_ONLY" -eq 1 ]]; then
  echo "${PUBLIC_APP_URL:-$(current_public_url)}"
  exit 0
fi

[[ -f .env ]] || { echo "Missing .env — chạy ./scripts/bootstrap.sh trước." >&2; exit 1; }

PUBLIC_URL=""

if [[ "$MODE" == "lan" ]]; then
  log "Phát hiện LAN IP (cùng Wi‑Fi với điện thoại)"
  LAN_IP="$(detect_lan_ip)"
  if [[ -z "$LAN_IP" ]]; then
    echo "Không phát hiện được LAN IP." >&2
    exit 1
  fi
  PUBLIC_URL="http://${LAN_IP}:${APP_PORT}"
  ok "LAN: $PUBLIC_URL"
else
  need_docker() {
    command -v docker >/dev/null 2>&1 || { echo "Cần docker." >&2; exit 1; }
  }
  need_docker

  log "Khởi động Cloudflare quick tunnel"
  docker compose --profile tunnel up -d tunnel
  ok "tunnel container started"

  log "Chờ URL trycloudflare.com…"
  PUBLIC_URL=""
  for i in $(seq 1 45); do
    PUBLIC_URL="$(docker compose logs tunnel 2>/dev/null | grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' | tail -1 || true)"
    if [[ -n "$PUBLIC_URL" ]]; then
      break
    fi
    sleep 2
  done
  if [[ -z "$PUBLIC_URL" ]]; then
    warn "Chưa thấy URL tunnel — xem: docker compose logs tunnel -f"
    exit 1
  fi
  ok "Tunnel: $PUBLIC_URL"
fi

log "Ghi PUBLIC_APP_URL vào .env"
set_env_var "PUBLIC_APP_URL" "$PUBLIC_URL"
ok "PUBLIC_APP_URL=$PUBLIC_URL"

if docker compose ps --status running app 2>/dev/null | grep -q cca-app; then
  log "Recreate app để nạp PUBLIC_APP_URL / APP_URL vào QR"
  export PUBLIC_APP_URL="$PUBLIC_URL"
  # shellcheck disable=SC1091
  set -a
  source <(grep -E '^(APP_KEY|DB_|REVERB_|APP_PORT)=' .env | sed 's/\r$//' || true)
  set +a
  docker compose up -d --force-recreate app
  ok "app recreated"

  for i in $(seq 1 30); do
    if curl -fsS "http://127.0.0.1:${APP_PORT}/up" >/dev/null 2>&1; then
      break
    fi
    sleep 1
  done
fi

cat <<EOF

────────────────────────────────────────────────────────
 Public URL cho QR / điểm danh
────────────────────────────────────────────────────────
 $PUBLIC_URL

 Điện thoại mở link QR hoặc:
   $PUBLIC_URL/check-in/{qr_token}

 Rồi nhập MSSV (vd: 102210001) → điểm danh.
────────────────────────────────────────────────────────
EOF
