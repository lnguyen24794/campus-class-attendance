# Log

## Kênh log (Laravel mặc định)

Cấu hình trong `config/logging.php`. Dev/demo thường dùng channel `stack` → `single` (`storage/logs/laravel.log`).

| Mục đích | Gợi ý level / channel |
|----------|------------------------|
| Lỗi ứng dụng bất ngờ | `error` / `critical` |
| Cảnh báo có thể phục hồi | `warning` |
| Sự kiện nghiệp vụ đáng lưu (mở buổi, check-in thành công) | `info` (khi cần audit) |
| Debug tạm thời | `debug` — không để spam trên production |

## Mức độ

| Level | Khi nào |
|-------|---------|
| CRITICAL | Hệ thống dừng / cần xử lý ngay |
| ERROR | Lỗi không mong đợi |
| WARNING | Lỗi dự kiến nhưng cần theo dõi |
| INFO | Sự kiện bình thường cần lưu |
| DEBUG | Chỉ phục vụ debug |

## Thực hành CCA

- Không log token QR đầy đủ / dữ liệu nhạy cảm
- Exception nghiệp vụ (`AttendanceException`) đã trả message cho client — chỉ log thêm khi cần điều tra
- Batch/command (nếu có sau này): tách channel `command` nếu volume lớn
