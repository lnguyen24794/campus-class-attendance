# Class Seeder

## Quy tắc chung

- Tên class: `{Noun}Seeder` hoặc mô tả rõ mục đích
- Gọi seeder con từ `DatabaseSeeder.php`
- Dùng Factory khi tạo dữ liệu test / demo hàng loạt

## Cấu trúc hiện tại

```
database/seeders/
├── DatabaseSeeder.php      # call DutCnttSeeder
└── DutCnttSeeder.php       # Khoa CNTT — ĐHBK Đà Nẵng
database/factories/
└── UserFactory.php         # + code, role states student/lecturer
```

## DutCnttSeeder (đã triển khai)

Dữ liệu thực tế chuyên ngành công nghệ tại **Trường Đại học Bách khoa — Đại học Đà Nẵng**:

| Nhóm | Nội dung |
|------|----------|
| Ngành | `7480201` CNTT · `7480201A` CNTT (Nhật) · `7480201B` KHDL & AI |
| Admin | `admin@dut.udn.vn` |
| Giảng viên | GV001… + alias `lecturer@cca.test` |
| Sinh viên | MSSV kiểu `10221xxxx` / `10222xxxx` / `10223xxxx` + alias `student1@cca.test`… |
| Môn | IT001–IT015 + CT101 (demo Web) |
| Tiết | 1–12 (khung giờ) |
| Lớp HP | 10 lớp, học kỳ `2025-2026.1` |
| Enrollment | map theo ngành → lớp tương ứng |

Password demo thống nhất: `password` (cast `hashed` trên User).

Tham chiếu công khai: [Khoa CNTT DUT](https://dut.udn.vn/KhoaCNTT/Gioithieu)

## Nguyên tắc dữ liệu demo

- Đủ: admin, GV, SV, môn, tiết, lớp (`course_sections`), enrollment
- `role` khớp middleware (`student` / `lecturer` / `admin`)
- Không hard-code secret thật — chỉ giá trị demo rõ ràng
- Dùng `updateOrCreate` / `firstOrCreate` để seed idempotent khi có thể

## Chạy

```bash
php artisan db:seed
php artisan migrate:fresh --seed
./scripts/bootstrap.sh          # full setup + seed
```
