# Code coverage (CCA)

**Yêu cầu:** lines + methods trên `app/` = **100%**.

## Xem báo cáo HTML (trình duyệt)

1. Cài [PCOV](https://github.com/krakjoe/pcov) (hoặc Xdebug) — macOS: `brew install shivammathur/extensions/pcov@8.4` (đổi đúng phiên bản PHP).
2. Sinh report:

```bash
composer coverage
```

3. Mở link (app đang chạy `php artisan serve` / Docker cổng 8000):

**→ [http://localhost:8000/coverage](http://localhost:8000/coverage)**

Hoặc mở file trực tiếp: `docs/coverage/html/index.html`  
(`composer coverage:open` trên macOS).

Hướng dẫn PCOV + HTML report: [Generate Code Coverage in Laravel With PCOV (Laravel News)](https://laravel-news.com/generate-code-coverage-in-laravel-with-pcov).

## File trong repo

| File | Mô tả |
|------|--------|
| [SUMMARY.md](SUMMARY.md) | Tóm tắt % (agent cập nhật mỗi prompt) |
| [coverage.txt](coverage.txt) | Báo cáo text PHPUnit |
| [clover.xml](clover.xml) | Clover (CI) |
| `html/` | HTML chi tiết — **gitignored**, sinh bằng `composer coverage` |
