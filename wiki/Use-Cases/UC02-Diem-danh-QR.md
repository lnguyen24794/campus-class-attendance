# UC02 — Điểm danh bằng mã QR

**Tác nhân:** Sinh viên (quét QR / check-in) · Giảng viên / Admin (mở buổi / phát QR)

## Mục tiêu (slide demo)

Check-in QR phải kiểm tra lần lượt 5 quy tắc; thành công → MSG-05 dạng:

```text
Bạn đã điểm danh {môn học} - {tiết học} - {trạng thái}
```

Ví dụ: `Bạn đã điểm danh Lập trình Web - Tiết 1 - Đúng giờ`

## Quy tắc nghiệp vụ

| ID | Quy tắc (slide) | Ánh xạ triển khai (source) | MSG |
|----|-----------------|----------------------------|-----|
| R01 | QR hết hạn sau **số phút / khung giờ đã cấu hình** | `qr_expires_at` = `closes_at` (theo tiết `ends_at` hoặc `duration_minutes`). Refresh QR **chỉ** đổi token, **không** đổi hạn | MSG-01 |
| R02 | Sinh viên phải thuộc lớp đó | Phải có `Enrollment` với `course_section_id` của buổi | MSG-02 |
| R03 | Mỗi buổi chỉ điểm danh **một lần** (một thiết bị / một SV) | Scope hiện tại: **một bản ghi attendance / (student, session)**. Không bắt buộc fingerprint thiết bị | MSG-03 |
| R04 | Buổi học phải đang mở | `status = open` và trong `opens_at`…`closes_at` | MSG-04 |
| R05 | Trạng thái **Đúng giờ** hoặc **Đi muộn** (sau N phút) | `late_after_minutes` (mặc định **15**): ≤ ngưỡng → Đúng giờ; sau → Đi muộn | MSG-05 |

## API (auth)

```
POST /api/v1/lecturers/attendance-sessions
POST /api/v1/students/attendances/check-in
Body: { "session_qr_token": "..." }
```

## Luồng web demo

1. GV/Admin mở buổi → `POST /api/v1/admin/attendance-sessions`
2. Live QR = `{PUBLIC_APP_URL}/check-in/{qr_token}` (**không** hiện Public URL trên UI)
3. SV nhập MSSV → `POST /api/v1/public/check-in/{qrToken}`
4. Response `message` = MSG-05 (`subject` - `period` - `status`)
5. Reverb → bảng Live realtime

## MSG-05 (bắt buộc)

| Placeholder | Nguồn |
|-------------|--------|
| `:subject` | `subject.name` → fallback tên/mã lớp HP |
| `:period` | `period.name` / `Tiết {number}` → fallback `Không gắn tiết` |
| `:status` | Label VI: **Đúng giờ** / **Đi muộn** |

Helper: `App\Support\AttendanceSuccessMessage::from($attendance)`  
Lang: `lang/vi/messages.php` → `MSG-05`

## Triển khai

- `AttendanceSessionService::createAttendanceSession` / `refreshQrToken`
- `AttendanceService::createAttendance` (+ thứ tự R01→R04 rồi R05)
- Controllers trả `AttendanceSuccessMessage::from(...)`
- Test: `AttendanceCheckInControllerTest`

## Ghi chú AI Agent (tránh lệch hướng)

- **Cấm** TTL QR cố định 60s / refresh cộng hạn.
- Label UI: **Đúng giờ** / **Đi muộn** (không «Muộn» / Present).
- Create form: prefill `duration_minutes` từ `period.ends_at − starts_at` (không tiết → 45).
- Chi tiết UI/Live: [Tổng hợp](../Huong-dan/Tong-hop-Lich-su-va-QR) · rule `.cursor/rules/cca-qr-attendance.mdc`

## Demo

```bash
php artisan test --filter=AttendanceCheckInControllerTest
```
