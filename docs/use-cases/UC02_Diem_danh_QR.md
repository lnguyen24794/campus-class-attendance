# UC02 — Điểm danh bằng mã QR lớp học phần

> **Trạng thái demo:** code triển khai đã **gỡ**. Recreate theo file này + `.cursor/rules/cca-qr-attendance.mdc`.

**Tác nhân:** Sinh viên (quét QR) · Giảng viên (mở buổi / phát QR)

## Quy tắc nghiệp vụ

| ID | Quy tắc | Thông báo |
|----|---------|-----------|
| R01 | QR hết hạn theo khung đã cấu hình (`qr_expires_at` = `closes_at`; refresh không đổi hạn) | MSG-01 |
| R02 | Sinh viên phải thuộc lớp học phần | MSG-02 |
| R03 | Mỗi buổi chỉ điểm danh **một lần** (1 attendance / student+session) | MSG-03 |
| R04 | Buổi phải đang **mở** trong khung giờ | MSG-04 |
| R05 | Thành công → Đúng giờ / Đi muộn theo `late_after_minutes` (mặc định 15) | MSG-05 |

## MSG-05 (thành công)

```
Bạn đã điểm danh :subject - :period - :status
```

VD: `Bạn đã điểm danh Lập trình Web - Tiết 1 - Đúng giờ`

Helper: `AttendanceSuccessMessage::from($attendance)`

## API

```
POST /api/v1/lecturers/attendance-sessions
POST /api/v1/students/attendances/check-in
POST /api/v1/public/check-in/{qrToken}
```

## Triển khai

- `AttendanceSessionService::createAttendanceSession` / `refreshQrToken`
- `AttendanceService::createAttendance` → `AttendanceResource` + MSG-05
- Test: `tests/Feature/Api/AttendanceCheckInControllerTest.php`

## Ghi chú AI

Rule Cursor: `.cursor/rules/cca-qr-attendance.mdc`  
Wiki đầy đủ: `campus-class-attendance.wiki/Use-Cases/UC02-Diem-danh-QR.md`
