# Unit / Feature Test

## Quy tắc chung

- **Coverage `app/` = 100%** (lines + methods) — bắt buộc trước khi kết thúc task / merge
- Public method **Service** / **Repository** / **Support** / **Rules** phải có Unit test
- **Controller** test qua API / Web endpoint (Feature test)
- Case độc lập — không phụ thuộc thứ tự chạy
- Không merge khi test fail hoặc coverage < 100%
- So sánh giá trị: ưu tiên `assertSame` (`===`); nếu dùng `assertEquals` thì ghi chú lý do

## Cấu trúc

```
tests/
├── Feature/
│   ├── Api/                 # Admin CRUD + dashboard
│   ├── Web/                 # Auth + Blade shells
│   └── Http/                # Middleware
└── Unit/
    ├── Services/
    ├── Repositories/
    ├── Models/
    ├── Enums/
    ├── Http/                # ApiResponse, Requests, Resources
    ├── Support/
    └── Providers/
```

- Feature: khớp routing / HTTP
- Unit: gọi trực tiếp class method
- Tên method: `test_{TargetMethod}_{Description}`

## Trọng tâm theo tầng

### Repository
- Input → query/ghi DB đúng → output đúng
- Cover nhánh `where` / filter (`q` rỗng vs có giá trị)

### Service
- Input → nhánh nghiệp vụ (default field, role mismatch…) → kết quả / exception đúng

### Controller (Feature / API)
- Thiếu auth → `401`
- Sai role → `403`
- Data hợp lệ → `2xx` / `201`
- Validation sai → `422`
- Sai loại resource (vd. lecturer id trên route student) → `404`

### FormRequest
- Presence-only + `moduleCommonRules()`
- `BaseListRequest::listFilters()` / strip empty query
- `BaseFormRequest` casts (kể cả nhánh lỗi)

## Chạy test & coverage

```bash
composer test
composer coverage
```

`composer coverage` ghi:

| File | Mục đích |
|------|----------|
| `docs/coverage/SUMMARY.md` | Tóm tắt % (agent cập nhật mỗi prompt khi đụng code/test) |
| `docs/coverage/coverage.txt` | Báo cáo text PHPUnit |
| `docs/coverage/clover.xml` | Clover XML |
| `public/coverage/` | HTML static (gitignored) — sinh bằng `artisan test --coverage` |

**Xem HTML:** [http://localhost:8000/coverage](http://localhost:8000/coverage) · `composer coverage:open`  
Laravel: [Reporting Test Coverage](https://laravel.com/framework/docs/testing#reporting-test-coverage) (`php artisan test --coverage --min=100`)

Cần **pcov** hoặc **xdebug**. macOS: `brew install shivammathur/extensions/pcov@8.4`. Hoặc `.php/pcov.so` (gitignored).

Cursor rule: `.cursor/rules/cca-test-coverage.mdc` (**alwaysApply**) — mỗi prompt đụng `app/`|`tests/` phải chạy lại coverage và cập nhật file report.

## Checklist DoD

- [ ] Unit/Feature cover đủ nhánh code mới
- [ ] `composer test` pass
- [ ] `composer coverage` → **100%** lines + methods
- [ ] `docs/coverage/SUMMARY.md` đã cập nhật
