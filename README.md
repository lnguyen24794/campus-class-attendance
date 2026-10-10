# Điểm danh lớp học phần bằng QR (CCA)

Đề tài demo **giả định** cho seminar — điểm danh lớp học phần bằng mã QR.

> Nội dung tài liệu: **tiếng Việt**; tên lớp / API / lệnh giữ tiếng Anh như trong code.

## Công nghệ

- Laravel 13 · PHP 8.4 · **PostgreSQL 16**
- Blade + [Flowbite Admin Dashboard](https://github.com/themesberg/flowbite-admin-dashboard)
- Frontend AJAX → `/api/v1/admin/*`
- Laravel Reverb + Echo · Cloudflare Tunnel (QR public)
- Seeder thực tế: **Khoa CNTT — Trường ĐH Bách khoa Đà Nẵng (DUT)**

## Chạy từ đầu đến cuối (khuyến nghị)

```bash
cd Seminar/campus-class-attendance
./scripts/bootstrap.sh
```

Script **tự** setup + `docker compose up` (Postgres + app + Reverb). Mở http://localhost:8000/login khi xong.

Tuỳ chọn:

```bash
./scripts/bootstrap.sh --tunnel          # + Cloudflare tunnel (QR qua internet)
./scripts/sync-public-url.sh --lan       # gắn LAN IP vào QR (cùng Wi‑Fi)
./scripts/sync-public-url.sh --tunnel    # bật tunnel + gắn PUBLIC_APP_URL
./scripts/bootstrap.sh --rebuild    # build lại image
./scripts/bootstrap.sh --no-up      # chỉ seed, không up container app
./scripts/bootstrap.sh --local-pg   # Postgres local (không Docker app)
```

Hoặc: `composer bootstrap`

## Docker full stack

```bash
docker compose up --build
# Postgres + app + reverb

docker compose --profile tunnel up
# + Cloudflare quick tunnel cho QR điện thoại
```

## Đăng nhập demo (sau seed)

| Email | Role | Password |
|-------|------|----------|
| `admin@dut.udn.vn` | admin | `password` |
| `lecturer@cca.test` | giảng viên | `password` |
| `student1@cca.test` / MSSV `102210001` | sinh viên | `password` |

## Dữ liệu seed DUT CNTT

Seeder: `database/seeders/DutCnttSeeder.php`

| Nhóm | Nội dung |
|------|----------|
| Ngành | `7480201` CNTT · `7480201A` CNTT Nhật · `7480201B` KHDL & AI |
| Môn | IT001–IT015 + CT101 (Web demo) |
| Tiết | Tiết 1–12 (khung giờ DUT) |
| Lớp HP | Lập trình Web, CSDL, CTDL, AI, ML, Mạng, ATTT… |
| SV | MSSV kiểu `10221xxxx` / `10222xxxx` / `10223xxxx` |

Tham chiếu: [Khoa CNTT DUT](https://dut.udn.vn/KhoaCNTT/Gioithieu)

## UI (Flowbite) + AJAX

| Trang Blade | API AJAX |
|-------------|----------|
| `/dashboard` | `GET /api/v1/admin/dashboard` |
| `/students` … `/periods` | CRUD `/api/v1/admin/*` |

> **UC01 / UC02 đã gỡ code** để demo recreate. Spec: `docs/use-cases/` + rule `.cursor/rules/cca-qr-attendance.mdc`.

## Test & coverage (100%)

```bash
composer test
composer coverage          # cần PCOV/Xdebug → ghi docs/coverage/
composer coverage:open     # sinh report + mở HTML (macOS)
```

**Xem HTML:** [http://localhost:8000/coverage](http://localhost:8000/coverage)  
(`composer coverage` → `public/coverage/` + `php artisan test --coverage --min=100`)

Chi tiết: [`docs/coverage/README.md`](docs/coverage/README.md) · Laravel: [Reporting Test Coverage](https://laravel.com/framework/docs/testing#reporting-test-coverage)

## Tài liệu

- `docs/use-cases/` — UC01, UC02 (blueprint recreate)
- `docs/use-cases/README.md` — prompt gợi ý khi demo
- `docs/architecture-rules.md` — quy ước kiến trúc
- Wiki (nếu clone kèm): `campus-class-attendance.wiki/`
