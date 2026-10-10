# Cài đặt & chạy

## Yêu cầu

- PHP ^8.3 (+ extension `pdo_pgsql`)
- Composer · Node.js
- Docker (khuyến nghị) hoặc PostgreSQL 16 local

## Cách nhanh nhất — script end-to-end (+ Docker tự chạy)

```bash
cd campus-class-attendance
./scripts/bootstrap.sh
```

**Mặc định** script sẽ:

1. Tạo/cập nhật `.env` (PostgreSQL + Reverb)
2. `composer install` · `npm install` · `npm run build`
3. Bật Postgres Docker
4. `migrate:fresh --seed` (`DutCnttSeeder`) trên host
5. Smoke test Feature
6. **`docker compose up -d`** (postgres + app + reverb) và chờ `http://localhost:8000/up`
7. **`./scripts/sync-public-url.sh --lan`** — gắn `PUBLIC_APP_URL=http://<LAN-IP>:8000` vào QR (điện thoại cùng Wi‑Fi)

Tuỳ chọn:

```bash
./scripts/bootstrap.sh --tunnel     # + Cloudflare tunnel (QR qua internet)
./scripts/bootstrap.sh --rebuild    # build lại image rồi up
./scripts/bootstrap.sh --no-up      # chỉ setup + seed, không up app container
./scripts/bootstrap.sh --local-pg   # Postgres local (không up Docker app)
./scripts/sync-public-url.sh --lan     # chỉ sync LAN IP
./scripts/sync-public-url.sh --tunnel  # chỉ bật tunnel + sync URL
composer bootstrap                  # alias composer script
```

Sau bootstrap mở: [http://localhost:8000/login](http://localhost:8000/login)

### Public URL cho QR (điện thoại)

| Chế độ | Lệnh | Ai truy cập được |
|--------|------|------------------|
| LAN (mặc định) | `./scripts/sync-public-url.sh --lan` | Điện thoại **cùng Wi‑Fi** |
| Internet | `./scripts/sync-public-url.sh --tunnel` | Mọi thiết bị có internet (Cloudflare quick tunnel) |

QR encode: `{PUBLIC_APP_URL}/check-in/{qr_token}` → form nhập **MSSV** → `POST /api/v1/public/check-in/{token}`.

## PostgreSQL

Mặc định trong `.env.example`:

| Key | Value |
|-----|-------|
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `cca` |
| `DB_USERNAME` | `cca` |
| `DB_PASSWORD` | `cca_secret` |

Docker service: `postgres` trong `docker-compose.yml` (volume `cca-postgres`).

Trong container, Compose ghi đè `DB_HOST=postgres`. Entrypoint chạy `php artisan serve --no-reload` để process HTTP nhận đúng biến môi trường Docker (tránh đọc `DB_HOST=127.0.0.1` từ `.env` host).

PHPUnit vẫn dùng SQLite `:memory:` (`phpunit.xml`) — không đụng Postgres demo.

## Sau bootstrap — chạy app

```bash
# Terminal 1 — HTTP (UI Flowbite + API)
php artisan serve

# Terminal 2 — WebSocket realtime
php artisan reverb:start

# Terminal 3 (tuỳ chọn) — public HTTPS cho điện thoại quét QR
docker compose --profile tunnel up tunnel
# Copy URL https://….trycloudflare.com vào:
#   PUBLIC_APP_URL=...
#   APP_URL=...
```

## Docker full stack

```bash
docker compose up --build
# postgres + app + reverb

docker compose --profile tunnel up
# + cloudflared
```

| Service | Port mặc định |
|---------|----------------|
| `app` | 8000 |
| `reverb` | 8080 |
| `postgres` | 5432 |

## Đăng nhập demo (seed DUT)

| Email / mã | Role | Password |
|------------|------|----------|
| `admin@dut.udn.vn` | admin | `password` |
| `lecturer@cca.test` | lecturer | `password` |
| `student1@cca.test` hoặc MSSV `102210001` | student | `password` |

Seeder: `database/seeders/DutCnttSeeder.php`

| Nhóm | Nội dung |
|------|----------|
| Ngành | `7480201` CNTT · `7480201A` CNTT Nhật · `7480201B` KHDL & AI |
| Môn | IT001–IT015 + CT101 (demo Web) |
| Tiết | 1–12 |
| Lớp HP | 10 lớp, học kỳ `2025-2026.1` |

Tham chiếu: [Khoa CNTT DUT](https://dut.udn.vn/KhoaCNTT/Gioithieu)

## Trang web sau khi chạy

| URL | Mô tả |
|-----|--------|
| `/login` | Đăng nhập session |
| `/dashboard` | Tổng quan (AJAX) |
| `/students` … `/periods` | CRUD master (AJAX + modal Flowbite) |
| `/sessions/create` | Mở buổi điểm danh |
| `/sessions/{id}/live` | QR + danh sách realtime |
| `/check-in/{qrToken}` | Form điểm danh public (scan QR) |

## Format & kiểm thử

```bash
vendor/bin/pint
php artisan test
```

## Tài liệu liên quan

- [Luồng nghiệp vụ](Luong-nghiep-vu)
- [Giao diện Flowbite & AJAX](Giao-dien-Flowbite-AJAX)
- [Realtime Reverb & public QR](Realtime-Reverb-QR)
- `README.md` trong repo app
