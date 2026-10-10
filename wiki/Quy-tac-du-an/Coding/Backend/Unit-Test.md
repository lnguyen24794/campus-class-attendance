# Unit / Feature Test

## Quy tắc chung

- Public method quan trọng của **Service** / **Repository** nên có test
- **Controller** test qua API endpoint (Feature test)
- Case độc lập — không phụ thuộc thứ tự chạy
- Không merge khi test fail
- So sánh giá trị: ưu tiên `assertSame` (`===`); nếu dùng `assertEquals` thì ghi chú lý do

## Cấu trúc

```
tests/
├── Feature/Api/
│   ├── StudentAttendanceControllerTest.php      # UC01
│   └── AttendanceCheckInControllerTest.php      # UC02
└── Unit/
    └── ...
```

- Feature: khớp routing / HTTP
- Unit: gọi trực tiếp class method
- Tên method gợi ý: `test_{TargetMethod}_{Description}` hoặc kiểu PHPUnit attribute hiện đại tương đương

## Trọng tâm theo tầng

### Repository
- Input → query/ghi DB đúng → output đúng
- Cover nhánh `where` / filter

### Service
- Input → nhánh nghiệp vụ (QR hết hạn, chưa enroll, đã điểm danh…) → kết quả / exception đúng

### Controller (Feature / API)
- Có auth: thiếu auth → `401`
- Sai role → `403`
- Data hợp lệ → `2xx`
- Validation sai → `422`
- Business rule (MSG-01…04) → status + `code` đúng

## Chạy test demo seminar

```bash
php artisan test

# UC01
php artisan test --filter=StudentAttendanceControllerTest

# UC02
php artisan test --filter=AttendanceCheckInControllerTest
```

## Checklist case nghiệp vụ (UC02)

| Case | Kỳ vọng |
|------|---------|
| QR hết hạn | MSG-01 |
| Không thuộc lớp | MSG-02 |
| Đã điểm danh | MSG-03 |
| Buổi chưa mở / đã đóng | MSG-04 |
| Thành công đúng giờ / muộn | MSG-05 + status |
