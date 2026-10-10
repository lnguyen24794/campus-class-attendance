# Tổng hợp — Lịch sử điểm danh & Điểm danh QR

Tài liệu neo để **recreate đúng source hiện tại**. Chi tiết rule: [UC01](../Use-Cases/UC01-Lich-su-diem-danh) · [UC02](../Use-Cases/UC02-Diem-danh-QR) · `.cursor/rules/cca-qr-attendance.mdc`.

## Sơ đồ nhanh

```
/sessions (lịch sử buổi)
    ↓ Mở buổi mới
/sessions/create  →  POST admin/attendance-sessions
    ↓ live_url
/sessions/{id}/live  ← Echo realtime
    ↓ QR encode
PUBLIC_APP_URL/check-in/{token}
    ↓ MSSV
POST public/check-in/{token}  →  Attendance + broadcast
```

## Checklist recreate

### Lịch sử buổi (`/sessions`)

- [ ] Blade + `sessions-history.js`
- [ ] `GET admin/attendance-sessions` + `IndexAttendanceSessionRequest`
- [ ] Search collapse mặc định
- [ ] Pagination admin-frontend (10/20/50, range, 4 nút)
- [ ] Lecturer scope `opened_by`

### Mở buổi + Live

- [ ] Options trả `starts_at`/`ends_at`/`duration_minutes` + `minutes_per_period: 45`
- [ ] Prefill duration khi chọn tiết
- [ ] `late_after_minutes` trên form (default 15)
- [ ] `qr_expires_at = closes_at` lúc tạo
- [ ] Refresh chỉ đổi token
- [ ] Live **không** Public URL
- [ ] Labels Đúng giờ / Đi muộn

### Check-in

- [ ] Public + student API cùng `AttendanceService`
- [ ] MSG-01…05
- [ ] Reverb `AttendanceCheckedIn`
- [ ] Feature test `AttendanceCheckInControllerTest`

## Không làm lại (đã sai trước đây)

- TTL QR 60 giây / refresh cộng hạn
- Hiện Public URL trên Live
- Gộp lịch sử buổi với lịch sử SV thành một API/UI
- Pagination số trang thay footer admin-frontend
