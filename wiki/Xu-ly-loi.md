# Xử lý lỗi

## Global handling

Laravel 11+/13 cấu hình exception trong `bootstrap/app.php`:

```php
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
);
```

Mọi request `/api/*` ưu tiên JSON error.

## Mã HTTP dùng trong CCA

| Status | Ý nghĩa | Ví dụ |
|--------|---------|-------|
| 401 | Chưa đăng nhập | Thiếu auth |
| 403 | Sai role | student gọi API lecturer |
| 404 | Không tìm thấy | Giảng viên mở lớp không thuộc mình |
| 422 | Validation / nghiệp vụ điểm danh | Thiếu field, MSG-01…04 |
| 500 | Lỗi không mong đợi | Exception chưa map |

## Envelope thành công (`ApiResponse`)

```json
{
  "success": true,
  "data": {},
  "message": "…"
}
```

## Lỗi nghiệp vụ điểm danh

Dùng `App\Exceptions\AttendanceException`:

- Factory: `qrExpired()`, `notEnrolled()`, `alreadyCheckedIn()`, `sessionNotOpen()`
- `render()`:
  - `/api/*` hoặc `expectsJson()` → envelope JSON tương thích `ApiResponse`
  - Web form (public check-in Blade) → `null` để Laravel/flow AJAX tự xử lý message

```json
{
  "success": false,
  "message": "…tiếng Việt…",
  "errors": null,
  "code": "MSG-01"
}
```

- Status mặc định: **422**
- Message từ `__('messages.MSG-xx')`

## Quy tắc viết trong Service

- Throw exception domain — **không** `return response()->json(...)`
- Ví dụ:

```php
if ($session->isQrExpired()) {
    throw AttendanceException::qrExpired();
}
```

## Validation Form Request

- Rule fail → Laravel **422** (validation bag)
- Business rule → `AttendanceException` (không nhầm với validation field)
