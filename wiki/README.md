# Campus Class Attendance — Wiki

Bộ wiki tiếng Việt cho demo **điểm danh lớp học phần bằng QR**.

**Bắt đầu:** [home.md](home.md)

## Đã triển khai (tóm tắt — khớp source)

- **UC01** Lịch sử: UI buổi `/sessions` + API SV attendances (hai khái niệm — xem UC01)
- **UC02** QR: mở buổi, Live, public check-in, Reverb
- QR hết hạn = `closes_at`; refresh token không đổi hạn
- `late_after_minutes` → Đúng giờ / Đi muộn
- Duration prefill theo tiết (`starts_at`/`ends_at`) hoặc 45’
- Admin CRUD + search collapse + pagination 10/20/50 (admin-frontend)
- PostgreSQL + Docker + `bootstrap.sh` + DutCnttSeeder
- CommonRules FormRequest

## Recreate / AI

Trước khi code lại UC01/UC02: đọc

1. [UC01](Use-Cases/UC01-Lich-su-diem-danh)
2. [UC02](Use-Cases/UC02-Diem-danh-QR)
3. Rule `.cursor/rules/cca-qr-attendance.mdc` trong repo app
