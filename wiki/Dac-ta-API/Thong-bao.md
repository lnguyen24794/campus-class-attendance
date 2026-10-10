# Mã thông báo (MSG)

Nguồn đúng trong code: `lang/vi/messages.php`  
Tham chiếu ngắn: `docs/messages.md`

| Mã | Nội dung (tiếng Việt) |
|----|------------------------|
| MSG-01 | Mã QR đã hết hạn. Vui lòng quét lại mã mới trên màn hình lớp học. |
| MSG-02 | Bạn không thuộc lớp học phần này. |
| MSG-03 | Bạn đã điểm danh buổi học này rồi. |
| MSG-04 | Buổi điểm danh chưa mở hoặc đã kết thúc. |
| MSG-05 | Bạn đã điểm danh :subject - :period - :status |

## Cách dùng trong code

```php
throw AttendanceException::qrExpired(); // MSG-01
// ...
AttendanceSuccessMessage::from($attendance);
// → "Bạn đã điểm danh Lập trình Web - Tiết 1 - Đúng giờ"
```

| Placeholder | Nguồn |
|-------------|--------|
| `:subject` | Tên môn (`subject.name`) / fallback tên hoặc mã lớp HP |
| `:period` | Tên tiết / `Tiết {n}` / `Không gắn tiết` |
| `:status` | **Đúng giờ** \| **Đi muộn** |

## Mapping quy tắc nghiệp vụ

| Rule | MSG |
|------|-----|
| R01 — QR hết hạn theo `closes_at` / tiết (refresh không đổi hạn) | MSG-01 |
| R02 — Phải thuộc lớp | MSG-02 |
| R03 — Điểm danh một lần | MSG-03 |
| R04 — Buổi đang mở | MSG-04 |
| R05 — Thành công MSG-05 (`môn - tiết - trạng thái`) | MSG-05 |

Khi thêm message mới: cập nhật `lang/vi/messages.php` + trang wiki này + test tương ứng.
