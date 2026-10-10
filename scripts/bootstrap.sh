#!/usr/bin/env bash
#
# CCA — chạy từ đầu đến cuối + tự khởi động Docker (Postgres + app + reverb).
#
# Usage:
#   ./scripts/bootstrap.sh              # setup + docker up + LAN PUBLIC_APP_URL (mặc định)
#   ./scripts/bootstrap.sh --local-pg   # Postgres local, không up app container
#   ./scripts/bootstrap.sh --no-up      # chỉ setup + migrate/seed, không docker up app
#   ./scripts/bootstrap.sh --tunnel     # + Cloudflare tunnel (QR qua internet)
#   ./scripts/bootstrap.sh --rebuild    # build lại image rồi up
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

MODE="docker"
DO_UP=1
DO_TUNNEL=0
DO_REBUILD=0

for arg in "$@"; do
  case "$arg" in
    --local-pg) MODE="local-pg"; DO_UP=0 ;;
    --no-up) DO_UP=0 ;;
    --tunnel) DO_TUNNEL=1; DO_UP=1 ;;
    --rebuild) DO_REBUILD=1; DO_UP=1 ;;
    --up) DO_UP=1 ;; # backward compatible
    -h|--help)
      sed -n '2,14p' "$0"
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

need() {
  command -v "$1" >/dev/null 2>&1 || {
    echo "Missing required command: $1" >&2
    exit 1
  }
}

need php
need composer
need npm
need docker

# --- 1. .env -----------------------------------------------------------------
log "Chuẩn bị file .env"
if [[ ! -f .env ]]; then
  cp .env.example .env
  ok "Đã copy .env.example → .env"
else
  ok ".env đã tồn tại"
fi

php -r '
$env = file_get_contents(".env");
$pairs = [
  "DB_CONNECTION" => "pgsql",
  "DB_HOST" => "127.0.0.1",
  "DB_PORT" => "5432",
  "DB_DATABASE" => "cca",
  "DB_USERNAME" => "cca",
  "DB_PASSWORD" => "cca_secret",
  "APP_FAKER_LOCALE" => "vi_VN",
  "BROADCAST_CONNECTION" => "reverb",
  "REVERB_APP_ID" => "cca-local",
  "REVERB_APP_KEY" => "cca-reverb-key",
  "REVERB_APP_SECRET" => "cca-reverb-secret",
  "REVERB_HOST" => "localhost",
  "REVERB_PORT" => "8080",
  "REVERB_SCHEME" => "http",
  "APP_URL" => "http://localhost:8000",
];
foreach ($pairs as $k => $v) {
  if (preg_match("/^{$k}=.*/m", $env)) {
    $env = preg_replace("/^{$k}=.*/m", "{$k}={$v}", $env);
  } else {
    $env .= "\n{$k}={$v}";
  }
}
if (!preg_match("/^VITE_REVERB_APP_KEY=/m", $env)) {
  $env .= "\nVITE_REVERB_APP_KEY=\"\${REVERB_APP_KEY}\"\nVITE_REVERB_HOST=\"\${REVERB_HOST}\"\nVITE_REVERB_PORT=\"\${REVERB_PORT}\"\nVITE_REVERB_SCHEME=\"\${REVERB_SCHEME}\"\n";
}
if (!preg_match("/^PUBLIC_APP_URL=/m", $env)) {
  $env .= "\nPUBLIC_APP_URL=\n";
}
file_put_contents(".env", $env);
'
ok "Đã cấu hình PostgreSQL + Reverb trong .env"

# --- 2. Composer / npm -------------------------------------------------------
log "Cài PHP dependencies"
composer install --no-interaction
ok "composer install"

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
  ok "php artisan key:generate"
else
  ok "APP_KEY đã có"
fi

# Export APP_KEY for docker compose variable substitution
set -a
# shellcheck disable=SC1091
source <(grep -E '^(APP_KEY|DB_|REVERB_|APP_PORT|PUBLIC_APP_URL)=' .env | sed 's/\r$//')
set +a

log "Cài Node dependencies + build assets"
npm install --ignore-scripts
npm run build
ok "npm build"

chmod +x docker/entrypoint.sh scripts/bootstrap.sh scripts/sync-public-url.sh 2>/dev/null || true

# --- 3. Docker Postgres -------------------------------------------------------
if [[ "$MODE" == "docker" ]]; then
  log "Khởi động PostgreSQL (Docker)"
  docker compose up -d postgres
  log "Chờ Postgres healthy…"
  for i in $(seq 1 45); do
    if docker compose exec -T postgres pg_isready -U cca -d cca >/dev/null 2>&1; then
      ok "Postgres sẵn sàng"
      break
    fi
    if [[ $i -eq 45 ]]; then
      echo "Postgres không sẵn sàng sau 45s" >&2
      docker compose logs postgres | tail -40 >&2
      exit 1
    fi
    sleep 1
  done
else
  log "Dùng Postgres local — kiểm tra kết nối"
  if ! php -r '
    try {
      new PDO("pgsql:host=127.0.0.1;port=5432;dbname=cca", "cca", "cca_secret");
      echo "ok";
    } catch (Throwable $e) {
      fwrite(STDERR, $e->getMessage() . PHP_EOL);
      exit(1);
    }
  '; then
    cat >&2 <<'EOF'
Không kết nối được Postgres local (cca@127.0.0.1/cca).
Hoặc chạy: ./scripts/bootstrap.sh   (Docker Postgres + full stack)
EOF
    exit 1
  fi
  ok "Kết nối Postgres local OK"
fi

# --- 4. Migrate + seed (host, nhanh để kiểm tra) ------------------------------
log "Migrate fresh + seed dữ liệu Khoa CNTT ĐHBK Đà Nẵng (host)"
php artisan config:clear
php artisan migrate:fresh --seed --force
ok "migrate:fresh --seed"

# --- 5. Smoke test ------------------------------------------------------------
log "Chạy test nhanh"
php artisan test --filter=AdminStudentControllerTest
ok "Feature test pass"

# --- 6. Docker up (mặc định) --------------------------------------------------
if [[ "$DO_UP" -eq 1 ]]; then
  log "Khởi động Docker stack (app + reverb + postgres)"
  BUILD_ARGS=()
  if [[ "$DO_REBUILD" -eq 1 ]]; then
    BUILD_ARGS+=(--build)
  else
    # Build once if image missing
    if ! docker image inspect cca-app:local >/dev/null 2>&1; then
      BUILD_ARGS+=(--build)
    fi
  fi

  # Recreate app/reverb so entrypoint + env overrides always apply
  if [[ "$DO_TUNNEL" -eq 1 ]]; then
    docker compose --profile tunnel up -d --force-recreate "${BUILD_ARGS[@]+"${BUILD_ARGS[@]}"}"
  else
    docker compose up -d --force-recreate "${BUILD_ARGS[@]+"${BUILD_ARGS[@]}"}" postgres reverb app
  fi
  ok "docker compose up -d"

  log "Chờ app healthy (HTTP /up)…"
  for i in $(seq 1 60); do
    if curl -fsS "http://127.0.0.1:${APP_PORT:-8000}/up" >/dev/null 2>&1; then
      ok "App đang chạy tại http://localhost:${APP_PORT:-8000}"
      break
    fi
    if [[ $i -eq 60 ]]; then
      warn "App chưa trả /up — xem log:"
      docker compose logs app --tail 80 >&2 || true
      docker compose logs reverb --tail 40 >&2 || true
      exit 1
    fi
    sleep 2
  done

  log "Smoke check trang login…"
  LOGIN_CODE="$(curl -sS -o /dev/null -w '%{http_code}' "http://127.0.0.1:${APP_PORT:-8000}/login" || true)"
  if [[ "$LOGIN_CODE" == "200" ]]; then
    ok "GET /login → 200"
  else
    warn "GET /login → ${LOGIN_CODE:-?} (kỳ vọng 200) — xem: docker compose logs app --tail 80"
    docker compose logs app --tail 40 >&2 || true
    exit 1
  fi

  # Public URL cho QR: LAN (cùng Wi‑Fi) hoặc Cloudflare tunnel (internet)
  log "Đồng bộ PUBLIC_APP_URL cho QR / điện thoại"
  if [[ "$DO_TUNNEL" -eq 1 ]]; then
    ./scripts/sync-public-url.sh --tunnel
  else
    ./scripts/sync-public-url.sh --lan
  fi
fi

PUBLIC_URL="$(grep -E '^PUBLIC_APP_URL=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '\r' || true)"

cat <<EOF

────────────────────────────────────────────────────────
 CCA bootstrap + Docker hoàn tất
────────────────────────────────────────────────────────
 Web:        http://localhost:${APP_PORT:-8000}/login
 Reverb:     ws://localhost:${REVERB_PORT:-8080}
 Public QR:  ${PUBLIC_URL:-"(chưa set — ./scripts/sync-public-url.sh)"}

 Tài khoản:
   admin@dut.udn.vn / password
   lecturer@cca.test / password
   student1@cca.test (MSSV 102210001) / password

 Lệnh hữu ích:
   docker compose ps
   docker compose logs -f app
   ./scripts/sync-public-url.sh --lan
   ./scripts/sync-public-url.sh --tunnel
   ./scripts/bootstrap.sh --rebuild
   ./scripts/bootstrap.sh --no-up
────────────────────────────────────────────────────────
EOF
