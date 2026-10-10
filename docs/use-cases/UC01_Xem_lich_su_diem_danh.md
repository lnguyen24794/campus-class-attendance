# UC01 — Xem lịch sử điểm danh

**Tác nhân:** Sinh viên (đã đăng nhập)

## Mục tiêu

- `GET /api/v1/students/attendances`
- Chỉ trả dữ liệu của **chính sinh viên đang đăng nhập**

## Luồng chính

1. Gọi API list attendances.
2. Hiển thị buổi đã điểm danh (mới nhất trước).
3. Mỗi dòng: lớp, buổi, trạng thái (Đúng giờ / Đi muộn), thời điểm.

## API

```
GET /api/v1/students/attendances?q=&page=1&limit=10
```

## Triển khai

- Controller: `StudentAttendanceController@index`
- Service: `AttendanceService::fetchAttendanceList`
- Repository: `AttendanceRepository::searchAttendances`
- Resource: `AttendanceResourceForList`
- Test: `tests/Feature/Api/StudentAttendanceControllerTest.php`

## Ghi chú AI

Menu UI `/sessions` «Lịch sử điểm danh» = lịch sử **buổi** (Admin/GV) — **không** phải UC01 API này.
Chi tiết wiki: `campus-class-attendance.wiki/Use-Cases/UC01-Lich-su-diem-danh.md`
