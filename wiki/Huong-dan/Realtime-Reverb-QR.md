# Realtime Reverb & public QR

## Reverb

| Thành phần | Giá trị |
|------------|---------|
| Package | `laravel/reverb` |
| Broadcast | `BROADCAST_CONNECTION=reverb` |
| Event | `App\Events\AttendanceCheckedIn` (`ShouldBroadcastNow`) |
| Channel | public `attendance-sessions.{sessionId}` |
| Event name | `.attendance.checked-in` |
| Client | Laravel Echo + `pusher-js` (`resources/js/echo.js`) |

### Chạy

```bash
php artisan reverb:start
# mặc định 0.0.0.0:8080
```

Env chính: `REVERB_APP_*`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` và cặp `VITE_REVERB_*`.

### Payload broadcast

```json
{
  "id": "...",
  "student_name": "...",
  "student_code": "...",
  "status": "present|late",
  "checked_in_at": "2026-10-10T02:00:00+00:00"
}
```

Phát khi `AttendanceService::createAttendance` thành công (API student hoặc public check-in).

## Public QR (điện thoại)

QR encode URL:

```text
{PUBLIC_APP_URL}/check-in/{qr_token}
```

`PublicUrlService` ưu tiên `config('app.public_url')` ← `PUBLIC_APP_URL`, fallback `APP_URL`.

Form public: nhập **MSSV** hoặc email → `POST /api/v1/public/check-in/{qrToken}`.

### Script đồng bộ URL (khuyến nghị)

```bash
# Cùng Wi‑Fi (LAN IP máy host)
./scripts/sync-public-url.sh --lan

# Mọi thiết bị có internet (Cloudflare quick tunnel)
./scripts/sync-public-url.sh --tunnel
```

Script ghi `PUBLIC_APP_URL` vào `.env` và recreate container `app` để QR dùng đúng base URL.

Hoặc kèm bootstrap:

```bash
./scripts/bootstrap.sh              # + LAN PUBLIC_APP_URL
./scripts/bootstrap.sh --tunnel     # + Cloudflare tunnel
```

### Luồng màn Live

1. `GET /api/v1/admin/attendance-sessions/{id}/live` → `qr_svg`, `check_in_url`, danh sách hiện có
2. Echo lắng nghe channel → prepend hàng mới khi có check-in
3. Nút «Làm mới QR» → **chỉ đổi `qr_token`**; `qr_expires_at` / `closes_at` **không đổi**
4. Nút «Sao chép link điểm danh» → copy `check_in_url`
5. **Không** hiển thị Public URL trên Live (base URL chỉ dùng khi tạo QR)

Payload live nên có `late_after_minutes` và `status_label` (**Đúng giờ** / **Đi muộn**).

## Xem thêm

- [Cài đặt](Cai-dat)
- [Public check-in API](../Dac-ta-API/Public-Check-in)
- [Admin CRUD](../Dac-ta-API/Admin-CRUD)
