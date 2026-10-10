# API — Public check-in (scan QR)

Dùng khi sinh viên mở URL trong mã QR (không cần đăng nhập API student).

Base: `/api/v1/public`

## Meta buổi

```
GET /api/v1/public/check-in/{qrToken}
```

```json
{
  "success": true,
  "data": {
    "qr_token": "...",
    "expired": false,
    "session": { "title": "...", "course_section_code": "..." }
  }
}
```

`expired = true` khi token không tồn tại, QR hết hạn, hoặc buổi không mở.

## Check-in

```
POST /api/v1/public/check-in/{qrToken}
Content-Type: application/json

{ "identifier": "102210001" }
```

`identifier`: **MSSV (`users.code`)** hoặc **email** sinh viên.

### Thành công `201`

`data`: `AttendanceResource`  
`message`: MSG-05 (localized)

Đồng thời broadcast `AttendanceCheckedIn` lên Reverb.

### Lỗi

| Trường hợp | Status | Ghi chú |
|------------|--------|---------|
| Không tìm thấy SV | 422 | validation `identifier` |
| MSG-01 … MSG-04 | 422 | cùng message nghiệp vụ như API student |

## Blade

`GET /check-in/{qrToken}` → shell `check-in.show` → JS `publicApi`.

## Triển khai

| Layer | Class |
|-------|--------|
| Controller | `Api\V1\Public\PublicCheckInController` |
| Service | `AttendanceService::createAttendance` |
| Lookup | `UserRepository::findUserByEmailOrCode` |

Xem thêm: [Realtime Reverb & public QR](../Huong-dan/Realtime-Reverb-QR) · [UC02](../Use-Cases/UC02-Diem-danh-QR)
