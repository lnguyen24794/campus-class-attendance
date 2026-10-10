# API — Sinh viên: check-in QR (UC02)

## Endpoint

```
POST /api/v1/students/attendances/check-in
```

## Auth

- Middleware: `auth`, `role:student`

## Body (JSON)

| Field | Type | Bắt buộc | Mô tả |
|-------|------|----------|-------|
| `session_qr_token` | string | Có | Token QR của buổi đang mở |

## Response thành công `201`

```json
{
  "success": true,
  "data": {
    "id": "...",
    "status": "present",
    "checked_in_at": "...",
    "course_section_code": "CT101-01",
    "session_title": "Buổi 3"
  },
  "message": "Điểm danh thành công (đúng giờ)."
}
```

- `data`: `AttendanceResource`
- `message`: MSG-05

## Lỗi nghiệp vụ `422`

```json
{
  "success": false,
  "message": "...",
  "errors": null,
  "code": "MSG-01"
}
```

| Code | Khi nào |
|------|---------|
| MSG-01 | QR không tồn tại / hết hạn (`qr_expires_at` = `closes_at`) |
| MSG-02 | Không thuộc lớp |
| MSG-03 | Đã điểm danh |
| MSG-04 | Buổi chưa mở / đã đóng |

## Triển khai

| Layer | Class |
|-------|-------|
| Request | `CheckInAttendanceRequest` (`BaseRequest`) |
| Controller | `StudentAttendanceController@checkIn` |
| Service | `AttendanceService::createAttendance` |
| Resource | `AttendanceResource` + `ApiResponse::created` |
| Exception | `AttendanceException` |
| Test | `tests/Feature/Api/AttendanceCheckInControllerTest.php` |
