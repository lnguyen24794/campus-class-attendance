# API — Giảng viên: tạo buổi điểm danh

## Endpoint (API lecturer)

```
POST /api/v1/lecturers/attendance-sessions
```

## Auth

- Middleware: `auth`, `role:lecturer`

## Body (JSON)

| Field | Type | Bắt buộc | Mô tả |
|-------|------|----------|-------|
| `course_section_id` | uuid | Có | Lớp do GV phụ trách |
| `title` | string | Có | Tên buổi |
| `period_id` | uuid | Không | Tiết (`exists:periods`) |
| `duration_minutes` | int | Không | 30–240; mặc định service **90** nếu không gửi (UI admin prefill 45 / theo tiết) |
| `late_after_minutes` | int | Không | 0–120; mặc định **15** |

Presence: `StoreAttendanceSessionRequest` — chỉ `REQUIRED`/`NULLABLE`.  
Type/exists: `AttendanceRules` (module).

## Response thành công `201`

```json
{
  "success": true,
  "data": {
    "session_id": "...",
    "qr_token": "...",
    "qr_expires_at": "...",
    "closes_at": "...",
    "title": "Buổi 3",
    "status": "open",
    "status_label": "Đang mở",
    "opens_at": "..."
  },
  "message": "Created successfully"
}
```

`data`: `AttendanceSessionResource`

## Hành vi nội bộ (khớp source)

| Tham số | Hành vi |
|---------|---------|
| Cửa sổ buổi | Có `period_id` → `closes_at` = `period.ends_at` hôm nay; `opens_at` = now (nếu còn trong tiết) |
| Không tiết | `closes_at` = now + `duration_minutes` |
| `qr_expires_at` | **Luôn = `closes_at`** |
| Refresh QR | Chỉ đổi `qr_token` — **không** đổi hạn |
| Ngưỡng muộn | `late_after_minutes` (mặc định 15) → Đúng giờ / Đi muộn |

Sai lớp / không phải lecturer của section → **404**

## Biến thể Admin UI (AJAX)

```
POST /api/v1/admin/attendance-sessions
```

Response thêm `live_url`. Prefill duration từ tiết: xem [UC02](../Use-Cases/UC02-Diem-danh-QR) · [Admin CRUD](Admin-CRUD).

## Triển khai

| Layer | Class |
|-------|--------|
| Request | `StoreAttendanceSessionRequest` |
| Controllers | `Lecturers\AttendanceSessionController@store`, `Admin\AttendanceSessionAdminController@store` |
| Service | `AttendanceSessionService::createAttendanceSession` / `refreshQrToken` |
| Resource | `AttendanceSessionResource` |
| Repository | `CourseSectionRepository`, `AttendanceSessionRepository` |

## Alias wiki

Trang cũ `Giang-vien-Mo-buoi` → dùng trang này (`Giang-vien-Tao-buoi`).
