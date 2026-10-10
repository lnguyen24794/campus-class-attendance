# Quy trình triển khai tính năng (CCA)

Áp dụng cho demo seminar / bài tập — rút gọn từ quy trình dự án thương mại, giữ tinh thần: **hiểu yêu cầu → thiết kế theo tầng → code + test → nghiệm thu**.

## Tổng quan

| Giai đoạn | Việc chính |
|-----------|------------|
| 1. Phân tích | Đọc UC / MSG / quy tắc nghiệp vụ; liệt kê câu hỏi còn mơ hồ |
| 2. Thiết kế | Impact: API, Service, Repository, migration, module *Rules, message, UI AJAX |
| 3. Implement | Code theo kiến trúc tầng + Feature/Unit test |
| 4. Kiểm tra | `pint` + `php artisan test` + checklist DoD + cập nhật wiki |

## Giai đoạn 1 — Phân tích

1. Đọc use case trong wiki `Use-Cases/` hoặc `docs/use-cases/`
2. Xác định tác nhân (`student` / `lecturer` / `admin`), API, MSG liên quan
3. Ghi QnA nếu spec chưa rõ — **ưu tiên câu Yes/No hoặc chọn Option**

| ID | Tham chiếu | Câu hỏi (Yes/No hoặc Option) | Trả lời | Status |
|----|------------|------------------------------|---------|--------|
| 01 | UC02 → R01 | QR hết hạn theo closes_at/tiết hay TTL 60s mỗi lần refresh? **Option A: = closes_at; refresh không đổi hạn** | A | Done |
| 02 | UC02 → R05 | Ngưỡng đúng giờ cố định 15’ hay cấu hình? **Option B: `late_after_minutes` (default 15)** | B | Done |
| 03 | UC02 create | Duration prefill? **Theo `period.ends_at − starts_at`; không tiết → 45’** | — | Done |
| 04 | UC01 | «Lịch sử điểm danh» UI = buổi (`/sessions`), khác API SV attendances | — | Done |

## Giai đoạn 2 — Thiết kế ngắn

Checklist impact:

- [ ] Endpoint mới / đổi contract? → cập nhật `Dac-ta-API/`
- [ ] Field mới → thêm đúng `Common\{Module}Rules` trước; API Request trong folder module, chỉ `REQUIRED`/`NULLABLE`
- [ ] Form Request + Resource?
- [ ] Service / Repository method nào?
- [ ] Migration / enum / message mới?
- [ ] Blade shell + AJAX page (`data-page`) nếu là UI admin?
- [ ] Broadcast Reverb nếu ảnh hưởng danh sách live?
- [ ] Test Feature cần case nào (happy + MSG lỗi)?

## Giai đoạn 3 — Implement

Thứ tự gợi ý:

1. Migration / Model / Enum (nếu có)
2. `Common\{Module}Rules` (field type/unique theo module)
3. Repository → Service
4. Request trong `Api/V1/{Module}/` (presence-only + `moduleCommonRules()`) + Resource + Controller + route
5. (Tuỳ) Blade shell + `resources/js/pages/*`
6. Feature test

Tuân thủ:

- Controller mỏng → Service → Repository
- `validated()` only
- Transaction trong Service khi ghi cần atomic
- Cursor rules: `.cursor/rules/laravel-form-request.mdc`, `cca-backend-architecture.mdc`

## Giai đoạn 4 — Kiểm tra

```bash
vendor/bin/pint
composer test
composer coverage
```

### Definition of Done (DoD)

- [ ] UC / MSG đã map đủ (nếu nghiệp vụ điểm danh)
- [ ] API wiki `Dac-ta-API/` đã cập nhật
- [ ] Field mới có trong đúng `{Module}Rules`; API Request không lặp type; không gom Request vào một folder chung
- [ ] Test liên quan pass; `composer coverage` = **100%**; `docs/coverage/SUMMARY.md` đã cập nhật
- [ ] Không lộ field nhạy cảm trong Resource
- [ ] Role middleware đúng
- [ ] (UI) AJAX gọi đúng endpoint; CSRF/session OK
- [ ] (Realtime) Event + channel documented nếu có broadcast

## Gắn với demo seminar

| Hạng mục | Lệnh / đường dẫn |
|----------|------------------|
| Bootstrap full | `./scripts/bootstrap.sh` |
| UC01 | `php artisan test --filter=StudentAttendanceControllerTest` |
| UC02 | `php artisan test --filter=AttendanceCheckInControllerTest` |
| Admin AJAX | `php artisan test --filter=AdminStudentControllerTest` |
| UI | `/dashboard`, `/students`, `/sessions/live` |
| Public QR | `/check-in/{token}` + tunnel |
