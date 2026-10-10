# Luồng nghiệp vụ

## A. Luồng API (UC01-B / UC02)

```
Giảng viên mở buổi
  → POST /api/v1/lecturers/attendance-sessions
  → qr_expires_at = closes_at (theo tiết hoặc duration)
  → late_after_minutes (mặc định 15)

Sinh viên gửi token
  → POST /api/v1/students/attendances/check-in
  → kiểm tra lần lượt:
       1. QR tồn tại & còn hạn (≤ closes_at)   (MSG-01)
       2. Buổi đang mở                        (MSG-04)
       3. Sinh viên đã enroll lớp             (MSG-02)
       4. Chưa điểm danh buổi này             (MSG-03)
  → tạo attendance:
       - present (Đúng giờ): ≤ opens_at + late_after_minutes
       - late (Đi muộn): sau ngưỡng trên
  → broadcast AttendanceCheckedIn (Reverb)
  → MSG-05
```

## B. Luồng web demo (Flowbite + AJAX + QR public)

```
Admin/GV đăng nhập (/login)
  → CRUD master: /students|/lecturers|/subjects|/periods
       · search collapse mặc định
       · pagination: 10/20/50 + "x-y của z" + first/prev/next/last
  → Lịch sử buổi: /sessions
       · GET /api/v1/admin/attendance-sessions
  → Mở buổi: /sessions/create
       · chọn tiết → prefill duration = ends_at − starts_at
       · POST /api/v1/admin/attendance-sessions
  → Live: /sessions/{id}/live
       · QR = PUBLIC_APP_URL/check-in/{token}
       · KHÔNG hiện Public URL
       · Làm mới QR = chỉ đổi token
       · Echo: attendance-sessions.{id}

Sinh viên (điện thoại)
  → /check-in/{qrToken}
  → POST /api/v1/public/check-in/{qrToken} { identifier: MSSV|email }
  → Live nhận .attendance.checked-in
```

## C. Public URL cho QR

```bash
./scripts/sync-public-url.sh --lan      # cùng Wi‑Fi
./scripts/sync-public-url.sh --tunnel   # Cloudflare Tunnel
```

Ghi `PUBLIC_APP_URL` vào `.env` — dùng khi generate QR (không phải dòng UI Live).

## Kiến trúc runtime

```
HTTP
  → BaseListRequest | BaseRequest (+ module *Rules)
  → Controller (Api\V1\* | Web\*)
  → Service
  → Repository
  → Model / PostgreSQL
  → (optional) Event → Reverb → Echo
```

## Thực thể chính

| Model | Vai trò |
|-------|---------|
| `User` | `student` / `lecturer` / `admin` (+ `code`) |
| `Subject` | Môn học |
| `Period` | Tiết (`starts_at` / `ends_at`) |
| `CourseSection` | Lớp học phần |
| `Enrollment` | SV ∈ lớp |
| `AttendanceSession` | Buổi + QR (`qr_expires_at` = `closes_at`, `late_after_minutes`) |
| `Attendance` | Bản ghi (`present` / `late`) |

## Xem thêm

- [UC01 — Lịch sử](../Use-Cases/UC01-Lich-su-diem-danh)
- [UC02 — QR](../Use-Cases/UC02-Diem-danh-QR)
- [Giao diện Flowbite & AJAX](Giao-dien-Flowbite-AJAX)
- [Realtime Reverb & public QR](Realtime-Reverb-QR)
