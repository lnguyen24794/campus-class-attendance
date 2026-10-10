# Định dạng code

## Công cụ

- **Laravel Pint** (đã có trong `composer.json` → `laravel/pint`)
- Dựa trên PHP CS Fixer / PSR-12

## Cách dùng

Từ thư mục gốc project `campus-class-attendance`:

```bash
# Format toàn bộ
vendor/bin/pint

# Chỉ kiểm tra, không sửa
vendor/bin/pint --test
```

## Lưu ý

- Chạy Pint trước khi tạo PR / merge
- Không commit code lệch style nếu CI/review bắt buộc format
- Ưu tiên quy tắc Pint hơn format mặc định của IDE
