#!/bin/sh
set -eu

ROLE="${1:-serve}"

log() { printf '%s\n' "$*"; }

wait_for_postgres() {
  if [ "${DB_CONNECTION:-}" != "pgsql" ]; then
    return 0
  fi

  log "Waiting for Postgres at ${DB_HOST:-postgres}:${DB_PORT:-5432}…"
  i=0
  while [ "$i" -lt 60 ]; do
    if php -r '
      $host = getenv("DB_HOST") ?: "postgres";
      $port = getenv("DB_PORT") ?: "5432";
      $db = getenv("DB_DATABASE") ?: "cca";
      $user = getenv("DB_USERNAME") ?: "cca";
      $pass = getenv("DB_PASSWORD") ?: "cca_secret";
      try {
        new PDO("pgsql:host={$host};port={$port};dbname={$db}", $user, $pass);
        exit(0);
      } catch (Throwable $e) {
        exit(1);
      }
    '; then
      log "Postgres is ready."
      return 0
    fi
    i=$((i + 1))
    sleep 1
  done

  log "Postgres did not become ready in time."
  exit 1
}

ensure_vendor() {
  # Named volume often starts empty (overrides image vendor/). Install with DEV
  # so host bootstrap/cache (Boost, Pint, …) stays compatible.
  if [ ! -f vendor/autoload.php ] || [ ! -d vendor/laravel/framework ]; then
    log "Installing Composer dependencies (including require-dev)…"
    composer install --no-interaction --prefer-dist
  elif [ ! -d vendor/laravel/boost ] && [ -f composer.json ]; then
    log "Syncing Composer dependencies (dev packages missing)…"
    composer install --no-interaction --prefer-dist
  fi
}

refresh_package_discovery() {
  # Host-mounted bootstrap/cache may reference packages not yet in vendor volume.
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
  php artisan package:discover --ansi >/dev/null 2>&1 || true
}

prepare_storage() {
  mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
  chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true
}

migrate_and_seed() {
  log "Running migrations…"
  php artisan migrate --force

  COUNT="$(php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    try {
      echo (int) Illuminate\Support\Facades\DB::table("users")->count();
    } catch (Throwable $e) {
      echo 0;
    }
  ' 2>/dev/null || echo 0)"

  if [ "$COUNT" = "0" ]; then
    log "Seeding DUT CNTT demo data…"
    php artisan db:seed --force
  else
    log "Database already seeded (users=${COUNT}), skip seed."
  fi
}

ensure_vendor
prepare_storage
refresh_package_discovery

case "$ROLE" in
  serve)
    wait_for_postgres
    migrate_and_seed
    # --no-reload: pass full $_ENV to the PHP built-in server child.
    # Without it, ServeCommand strips DB_HOST etc. and the child reloads
    # host .env (DB_HOST=127.0.0.1) → connection refused inside Docker.
    log "Starting Laravel HTTP server on :8000"
    exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
    ;;
  reverb)
    wait_for_postgres
    log "Starting Reverb on :8080"
    exec php artisan reverb:start --host=0.0.0.0 --port=8080
    ;;
  migrate)
    wait_for_postgres
    migrate_and_seed
    ;;
  *)
    exec "$@"
    ;;
esac
