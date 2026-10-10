# Đặc tả API chung

## Định dạng thời gian

- ISO 8601 extended, ví dụ: `2026-09-13T10:00:00+07:00`
- Field thời gian trong JSON dùng ATOM / `toAtomString()`

## Response thành công

| Thao tác | Method | Status | Body |
|----------|--------|--------|------|
| Danh sách | GET | 200 | Collection / paginator Resource |
| Chi tiết | GET | 200 | Resource |
| Tạo / mở | POST | 200 hoặc 201 | `{ "data": ... }` hoặc Resource |
| Cập nhật | PUT/PATCH | 200 | Resource |
| Xóa | DELETE | 200 | Rỗng hoặc message thống nhất |

## Khi không có dữ liệu

| Thao tác | Status | Body |
|----------|--------|------|
| List rỗng | 200 | Mảng / paginator rỗng |
| Chi tiết không tồn tại | 404 | JSON lỗi |
| Resource không thuộc user (lecturer mở sai lớp) | 404 | JSON lỗi |

## Lỗi thường gặp

| Status | Ý nghĩa |
|--------|---------|
| 401 | Chưa xác thực |
| 403 | Sai quyền (role) |
| 404 | Không tìm thấy |
| 422 | Validation hoặc lỗi nghiệp vụ điểm danh (`AttendanceException`) |
| 500 | Lỗi server không mong đợi |

## Envelope

- Ưu tiên bọc payload trong `data` khi trả mảng kết quả use case (check-in, mở buổi)
- List UC: Resource collection / pagination Laravel
- List Admin AJAX: `{ items: ResourceForList[], meta: { current_page, last_page, per_page, total } }` trong `data`

## Nhóm API

| Prefix | Auth | Mục đích |
|--------|------|----------|
| `/api/v1/students` | student | UC01 / UC02 |
| `/api/v1/lecturers` | lecturer | Mở buổi |
| `/api/v1/admin` | admin/lecturer + session/CSRF | CRUD + live QR (UI) |
| `/api/v1/public` | none | Check-in từ quét QR |

## Versioning

- Prefix: `/api/v1/...`
- Đổi breaking change → tăng version hoặc cập nhật wiki `Dac-ta-API/` + test
