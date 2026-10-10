# Tổng quan API

Base path: `/api/v1`

## Nghiệp vụ điểm danh (UC)

| Method | Path | Role | Mô tả |
|--------|------|------|-------|
| GET | `/students/attendances` | student | Lịch sử điểm danh (UC01) |
| POST | `/students/attendances/check-in` | student | Điểm danh bằng QR (UC02) |
| POST | `/lecturers/attendance-sessions` | lecturer | Mở buổi + phát QR |

Chi tiết: [Sinh viên — lịch sử](Sinh-vien-Lich-su) · [Check-in](Sinh-vien-Check-in) · [Giảng viên — mở buổi](Giang-vien-Tao-buoi)

## Admin CRUD (AJAX từ Flowbite UI)

Middleware: `web` + `auth` + `role:admin,lecturer` (session cookie + CSRF)

| Method | Path | Mô tả |
|--------|------|-------|
| GET | `/admin/dashboard` | Counters + buổi đang mở |
| * | `/admin/students` | CRUD sinh viên |
| * | `/admin/lecturers` | CRUD giảng viên |
| * | `/admin/subjects` | CRUD môn học |
| * | `/admin/periods` | CRUD tiết học |
| GET | `/admin/attendance-sessions/options` | Select lớp / tiết |
| POST | `/admin/attendance-sessions` | Mở buổi (AJAX) |
| GET | `/admin/attendance-sessions/{id}/live` | QR SVG + danh sách |
| POST | `/admin/attendance-sessions/{id}/refresh-qr` | Làm mới QR |

Chi tiết: [Admin CRUD](Admin-CRUD)

## Public (scan QR)

| Method | Path | Auth | Mô tả |
|--------|------|------|-------|
| GET | `/public/check-in/{qrToken}` | none | Meta buổi |
| POST | `/public/check-in/{qrToken}` | none | Check-in bằng mã SV / email |

Chi tiết: [Public check-in](Public-Check-in)

## Middleware

```php
Route::middleware(['auth', 'role:student'])->prefix('students')->group(...);
Route::middleware(['auth', 'role:lecturer'])->prefix('lecturers')->group(...);
Route::middleware(['web', 'auth', 'role:admin,lecturer'])->prefix('admin')->group(...);
// public: không auth
```

## FormRequest

Mọi body/query API dùng `mergeCommonRules()` — type/unique nằm ở `CommonRules`. Xem [Class Request](../Quy-tac-du-an/Coding/Backend/Class-Request).

## Thông báo

- [MSG-01 → MSG-05](Thong-bao)
