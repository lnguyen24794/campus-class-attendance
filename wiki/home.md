# Wiki — Campus Class Attendance (CCA)

Bộ hướng dẫn dự án demo **điểm danh lớp học phần bằng mã QR** (Laravel).

> Nội dung wiki: **tiếng Việt**. Tên lớp / API / lệnh giữ **tiếng Anh** như trong code.

## Trạng thái triển khai (đã có trong code)

| Hạng mục | Trạng thái | Ghi chú |
|----------|------------|---------|
| API UC01 / UC02 | ✅ | Student history + QR check-in + lecturer open |
| Lịch sử buổi (UI) | ✅ | `/sessions` — search + pagination admin-frontend |
| Mở buổi + Live QR | ✅ | `/sessions/create`, `/sessions/{id}/live` |
| QR expiry | ✅ | `qr_expires_at` = `closes_at`; refresh **không** reset hạn |
| Late window | ✅ | `late_after_minutes` (default 15) → Đúng giờ / Đi muộn |
| Duration prefill | ✅ | Theo `period` start/end; mặc định 45’/tiết |
| Master data CRUD | ✅ | SV, GV, môn, tiết — search collapse + pagination |
| UI Flowbite + AJAX | ✅ | Blade shell → `/api/v1/admin/*` · light-only |
| PostgreSQL + Docker | ✅ | `docker compose` + `scripts/bootstrap.sh` |
| Seeder Khoa CNTT DUT | ✅ | `DutCnttSeeder` |
| Reverb realtime | ✅ | Channel `attendance-sessions.{id}` |
| Public QR (LAN/tunnel) | ✅ | `PUBLIC_APP_URL` — **không** hiện trên Live |
| CommonRules FormRequest | ✅ | API Request chỉ `REQUIRED`/`NULLABLE` |

## Trước khi tạo PR / merge

```bash
vendor/bin/pint
php artisan test
```

## Mục lục

### Hướng dẫn nhanh
- [Cài đặt & chạy](Huong-dan/Cai-dat) — bootstrap Postgres, seed DUT, Reverb, tunnel
- [Tổng hợp Lịch sử + QR](Huong-dan/Tong-hop-Lich-su-va-QR) — checklist recreate
- [Luồng nghiệp vụ](Huong-dan/Luong-nghiep-vu) — API + web QR + realtime
- [Giao diện Flowbite & AJAX](Huong-dan/Giao-dien-Flowbite-AJAX)
- [Realtime Reverb & public QR](Huong-dan/Realtime-Reverb-QR)

### Use Cases
- [UC01 — Xem lịch sử điểm danh](Use-Cases/UC01-Lich-su-diem-danh)
- [UC02 — Điểm danh bằng QR](Use-Cases/UC02-Diem-danh-QR)

### Đặc tả API
- [Đặc tả API chung](Quy-tac-du-an/Dac-ta-API-chung)
- [Tổng quan endpoint](Dac-ta-API/Tong-quan)
- [Admin CRUD (AJAX)](Dac-ta-API/Admin-CRUD)
- [Sinh viên — lịch sử điểm danh](Dac-ta-API/Sinh-vien-Lich-su)
- [Sinh viên — check-in QR](Dac-ta-API/Sinh-vien-Check-in)
- [Giảng viên — mở buổi](Dac-ta-API/Giang-vien-Tao-buoi)
- [Public check-in](Dac-ta-API/Public-Check-in)
- [Mã thông báo (MSG)](Dac-ta-API/Thong-bao)

### Quy tắc dự án
- [Triển khai tính năng](Trien-khai-tinh-nang)
- [Xử lý lỗi](Xu-ly-loi)
- [Quy tắc Laravel](Quy-tac-du-an/Coding/Backend/Quy-tac-Laravel)
- [Định dạng code (Pint)](Quy-tac-du-an/Coding/Backend/Dinh-dang-code)
- [Ví dụ đặt tên](Quy-tac-du-an/Coding/Backend/Vi-du-dat-ten)

### Class / Layer
| Trang | Vai trò |
|-------|---------|
| [Controller](Quy-tac-du-an/Coding/Backend/Class-Controller) | API + Admin AJAX + Web Blade shell |
| [Service](Quy-tac-du-an/Coding/Backend/Class-Service) | Logic nghiệp vụ |
| [Repository](Quy-tac-du-an/Coding/Backend/Class-Repository) | Truy cập CSDL |
| [Request](Quy-tac-du-an/Coding/Backend/Class-Request) | CommonRules + presence-only |
| [Resource](Quy-tac-du-an/Coding/Backend/Class-Resource) | `Resource` / `ResourceForList` |
| [ApiResponse](Quy-tac-du-an/Coding/Backend/Class-ApiResponse) | Envelope JSON |
| [PHPDoc](Quy-tac-du-an/Coding/Backend/PHPDoc) | Comment chuẩn |
| [Model](Quy-tac-du-an/Coding/Backend/Class-Model) | Eloquent (+ Subject, Period) |
| [Migration](Quy-tac-du-an/Coding/Backend/Class-Migration) | Schema |
| [Seeder](Quy-tac-du-an/Coding/Backend/Class-Seeder) | DutCnttSeeder |
| [Transaction](Quy-tac-du-an/Coding/Backend/Transaction) | Giao dịch DB |
| [Unit Test](Quy-tac-du-an/Coding/Backend/Unit-Test) | Kiểm thử |
| [Log](Quy-tac-du-an/Coding/Backend/Log) | Ghi log |

### AI Agents (Cursor)
- Rule luôn bật: `campus-class-attendance/.cursor/rules/cca-backend-architecture.mdc`
- Rule FormRequest: `campus-class-attendance/.cursor/rules/laravel-form-request.mdc`
- Rule QR / lịch sử: `campus-class-attendance/.cursor/rules/cca-qr-attendance.mdc` (**bắt buộc** khi recreate UC01/UC02)

### Recreate từ wiki
Khi user yêu cầu **tạo lại** Lịch sử điểm danh / Điểm danh QR: đọc **UC01 + UC02 + rule `cca-qr-attendance`** trước — target = **source hiện tại đã document**, không spec TTL 60s cũ.

## Quy tắc chung (tóm tắt)

### Định dạng thời gian
- ISO 8601 (extended), ví dụ: `2026-09-13T10:00:00+07:00`
- Trong Resource dùng `toAtomString()`

### Đặt tên
- Ngôn ngữ code: tiếng Anh
- Tránh viết tắt trừ khi phổ biến (`id`, `qr`, `api`)
- Ưu tiên rõ ràng và nhất quán toàn codebase

### Kiến trúc tầng

```
HTTP Request
  → BaseListRequest | BaseRequest (+ CommonRules)
  → Controller
       → list:   ResourceForList / items+meta
       → create: ApiResponse::created(Resource)
  → Service (fetch*/create*/update*/delete*)
  → Repository (find/search/insert/update/delete)
  → Model / PostgreSQL
```

- Controller mỏng; truyền `$request->validated()`
- FormRequest API **chỉ** `REQUIRED`/`NULLABLE` — type/unique ở `CommonRules`
- Service chỉ inject Repository; **không** gọi `Model::query()` trực tiếp (trừ admin dashboard đếm đơn giản đã tách API)
- Đổi API → cập nhật wiki `Dac-ta-API/`

## Công nghệ

| Thành phần | Giá trị |
|------------|---------|
| Framework | Laravel 13 |
| PHP | ^8.3 (`pdo_pgsql`) |
| DB (app) | **PostgreSQL 16** |
| DB (phpunit) | SQLite `:memory:` |
| UI | Blade + Flowbite Admin Dashboard + Tailwind 4 |
| Realtime | Laravel Reverb + Echo (`pusher-js`) |
| QR | `simplesoftwareio/simple-qrcode` (SVG) |
| Public URL | Cloudflare Tunnel (`cloudflared`) / `PUBLIC_APP_URL` |
| Formatter | Laravel Pint |
| Test | PHPUnit |

## Liên kết trong repo

| Đường dẫn | Nội dung |
|-----------|----------|
| `README.md` | Chạy nhanh + endpoint |
| `scripts/bootstrap.sh` | Setup end-to-end |
| `docker-compose.yml` | Postgres + app + reverb + tunnel |
| `docs/architecture-rules.md` | Quy ước kiến trúc ngắn |
| `docs/use-cases/` | UC01, UC02 |
| `docs/messages.md` | MSG-01 → MSG-05 |
| `lang/vi/messages.php` | Nguồn message tiếng Việt |
