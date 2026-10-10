# API — Admin CRUD & session (AJAX)

Base: `/api/v1/admin`  
Auth: session (`web` + `auth` + `role:admin,lecturer`) + CSRF `X-CSRF-TOKEN`

Envelope: `ApiResponse` → `{ success, data, message }`

> Route names: `admin.students.*`, … — **không** trùng tên Blade (`students.index` → `/students`).

---

## Dashboard

```
GET /admin/dashboard
```

`data.counts`: `students`, `lecturers`, `subjects`, `periods`  
`data.open_sessions[]`: `id`, `title`, `course_section_code`, `period_name`, `live_url`

---

## Master resources (REST)

| Resource | Path | Body chính |
|----------|------|------------|
| Students | `/admin/students` | `code`, `name`, `email`, `password` |
| Lecturers | `/admin/lecturers` | như trên |
| Subjects | `/admin/subjects` | `code`, `name`, `credits`, `description?` |
| Periods | `/admin/periods` | `number`, `name`, `starts_at` (`H:i`), `ends_at` |

### List — search + pagination

```
GET /admin/{resource}?q=&page=1&limit=10
```

Alias: `per_page` ≡ `limit`. Mặc định `limit=10`. UI options: **10 / 20 / 50**.

```json
{
  "success": true,
  "data": {
    "items": [],
    "meta": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 10,
      "total": 18,
      "from": 1,
      "to": 10
    }
  }
}
```

Request: `Index{Student|Lecturer|Subject|Period}Request` (`q`, `page`, `limit`, `per_page`) — folder theo module.

### Create / Update / Delete

| Method | Path | Status |
|--------|------|--------|
| POST | `/admin/{resource}` | 201 |
| GET | `/admin/{resource}/{id}` | 200 |
| PUT/PATCH | `/admin/{resource}/{id}` | 200 |
| DELETE | `/admin/{resource}/{id}` | 200 |

Presence: `Store*` / `Update*` — chỉ `REQUIRED`/`NULLABLE`. Type/unique: `Common\{Module}Rules`.

---

## Attendance sessions

### Options (create form)

```
GET /admin/attendance-sessions/options
```

```json
{
  "sections": [{ "id": "...", "label": "..." }],
  "periods": [{
    "id": "...",
    "label": "Tiết 1 — … (07:00–07:50)",
    "number": 1,
    "starts_at": "07:00",
    "ends_at": "07:50",
    "duration_minutes": 50
  }],
  "minutes_per_period": 45
}
```

### Lịch sử buổi (UC01-A)

```
GET /admin/attendance-sessions?q=&status=&page=1&limit=10
```

Request: `IndexAttendanceSessionRequest`.  
Xem [UC01](../Use-Cases/UC01-Lich-su-diem-danh).

### Mở buổi (UC02)

```
POST /admin/attendance-sessions
{
  "course_section_id": "...",
  "title": "Buổi điểm danh",
  "period_id": null,
  "duration_minutes": 45,
  "late_after_minutes": 15
}
→ 201 { "session": AttendanceSessionResource, "live_url": "..." }
```

| Field nội bộ khi tạo | Giá trị |
|----------------------|---------|
| `qr_expires_at` | **= `closes_at`** (kết thúc tiết hoặc now+duration) |
| `late_after_minutes` | mặc định 15 |
| Refresh sau này | **chỉ** đổi `qr_token` |

### Live

```
GET /admin/attendance-sessions/{session}/live
```

```json
{
  "session": {
    "id": "...",
    "title": "...",
    "course_section_code": "...",
    "period_name": "...",
    "qr_expires_at": "...",
    "opens_at": "...",
    "closes_at": "...",
    "late_after_minutes": 15,
    "status": "open",
    "status_label": "Đang mở"
  },
  "check_in_url": "https://…/check-in/{token}",
  "qr_svg": "<svg…>",
  "attendances": [
    {
      "id": "...",
      "student_code": "...",
      "student_name": "...",
      "status": "present",
      "status_label": "Đúng giờ",
      "checked_in_at": "..."
    }
  ]
}
```

**Không** trả / không hiện `public_base_url` trên UI Live.

### Refresh QR

```
POST /admin/attendance-sessions/{session}/refresh-qr
→ cùng shape với live
```

**Rule:** response có `qr_token` mới; `qr_expires_at` / `closes_at` **giữ nguyên**.

---

## Triển khai

| Layer | Class |
|-------|--------|
| Controllers | `Api\V1\Admin\*`, `AttendanceSessionAdminController`, `DashboardController` |
| List requests | `IndexStudentRequest`, `IndexLecturerRequest`, `IndexSubjectRequest`, `IndexPeriodRequest` (+ `IndexAttendanceSessionRequest` khi có UC) |
| Services | `MasterUserService`, `SubjectService`, `PeriodService`, `AttendanceSessionService`, `PublicUrlService` |
| Meta | `App\Support\PaginationMeta` |

UI: [Giao diện Flowbite & AJAX](../Huong-dan/Giao-dien-Flowbite-AJAX) · [UC02](../Use-Cases/UC02-Diem-danh-QR)
