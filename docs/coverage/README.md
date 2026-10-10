# Code coverage (CCA)

**Yêu cầu:** lines + methods trên `app/` = **100%** (`--min=100`).

Laravel built-in: [Reporting Test Coverage](https://laravel.com/framework/docs/testing#reporting-test-coverage) (`php artisan test --coverage`).

## Xem báo cáo HTML

1. Cài [PCOV](https://pecl.php.net/package/pcov) hoặc Xdebug.
2. Sinh report (HTML ghi vào `public/coverage/` — static):

```bash
composer coverage
# tương đương: php artisan test --coverage --min=100 --coverage-html=public/coverage
```

3. Mở:

**→ [http://localhost:8000/coverage](http://localhost:8000/coverage)**

Report HTML nằm ở `public/coverage/` (sinh bởi `--coverage-html`). Route local inject `<base href="/coverage/">` để **click folder/file** (`Enums/`, `_css/`, …) hoạt động đúng.

CLI Laravel tương đương: `php artisan test --coverage --min=100` — xem [Reporting Test Coverage](https://laravel.com/framework/docs/testing#reporting-test-coverage).

## File trong repo

| File | Mô tả |
|------|--------|
| [SUMMARY.md](SUMMARY.md) | Tóm tắt % |
| [coverage.txt](coverage.txt) | Text PHPUnit |
| [clover.xml](clover.xml) | Clover (CI) |
| `public/coverage/` | HTML — **gitignored**, sinh bằng `composer coverage` |
