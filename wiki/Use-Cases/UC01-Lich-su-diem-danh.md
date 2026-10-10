# UC01 — Xem lịch sử điểm danh

**Tác nhân:** Sinh viên (đã đăng nhập)

## Mục tiêu (slide demo)

- Endpoint: `GET /api/v1/students/attendances`
- **Chỉ trả về dữ liệu của chính sinh viên đang đăng nhập** (không xem được buổi/điểm danh của người khác).

## Luồng chính

1. Sinh viên mở màn lịch sử điểm danh (client API / app).
2. Hệ thống trả danh sách lần điểm danh của **chính user đó** (mới nhất trước).
3. Mỗi dòng: mã/tên lớp, tiêu đề buổi, trạng thái (**Đúng giờ** / **Đi muộn**), thời điểm.

## API

```
GET /api/v1/students/attendances?q=&page=1&limit=10
```

Chi tiết: [Sinh viên — lịch sử](../Dac-ta-API/Sinh-vien-Lich-su)

## Triển khai

- Controller: `StudentAttendanceController@index`
- Service: `AttendanceService::fetchAttendanceList` (filter theo `$student->id`)
- Repository: `AttendanceRepository::searchAttendances`
- Resource: `AttendanceResourceForList`
- Test: `tests/Feature/Api/StudentAttendanceControllerTest.php`

## Ghi chú AI Agent (mở rộng UI demo — không thay UC slide)

Trong web admin còn có menu **«Lịch sử điểm danh»** → `/sessions` = danh sách **buổi** (Admin/GV).  
**Không** gộp với UC01 API sinh viên. Spec UI buổi: [Tổng hợp Lịch sử + QR](../Huong-dan/Tong-hop-Lich-su-va-QR).

## Demo

```bash
php artisan test --filter=StudentAttendanceControllerTest
```
