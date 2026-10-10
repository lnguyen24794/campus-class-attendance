# API — Sinh viên: lịch sử lần điểm danh (UC01-B)

> Khác với menu UI **«Lịch sử điểm danh»** (`/sessions` = lịch sử **buổi**). Xem [UC01](../Use-Cases/UC01-Lich-su-diem-danh).

## Endpoint

```
GET /api/v1/students/attendances
```

## Auth

- Middleware: `auth`, `role:student`

## Query

| Field | Type | Bắt buộc | Mặc định | Mô tả |
|-------|------|----------|----------|-------|
| `q` | string | Không | | Tìm theo mã/tên lớp HP hoặc tiêu đề buổi |
| `page` | int | Không | `1` | Trang |
| `limit` / `per_page` | int | Không | `10` | 1–100 |

Request: `IndexStudentAttendanceRequest` → `listFilters()`.

## Response `200`

`AttendanceResourceForList` collection (Laravel paginator meta):

| Field | Mô tả |
|-------|--------|
| `id` | ID điểm danh |
| `status` | `present` \| `late` |
| `status_label` | **Đúng giờ** \| **Đi muộn** |
| `checked_in_at` | ATOM |
| `course_section_code` / `name` | Lớp học phần |
| `session_title` | Tên buổi |

## Triển khai

| Layer | Class |
|-------|--------|
| Request | `IndexStudentAttendanceRequest` |
| Controller | `StudentAttendanceController@index` |
| Service | `AttendanceService::fetchAttendanceList` |
| Repository | `AttendanceRepository::searchAttendances` |
| Resource | `AttendanceResourceForList` |
| Test | `tests/Feature/Api/StudentAttendanceControllerTest.php` |
