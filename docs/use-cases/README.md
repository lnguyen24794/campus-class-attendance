# Use Cases — blueprint demo recreate

**Triển khai UC01 / UC02 đã được gỡ khỏi code** (controllers, routes, services, UI, tests).

Giữ lại để recreate khi demo:

| Giữ | Mục đích |
|-----|----------|
| `UC01_Xem_lich_su_diem_danh.md` | Spec UC01 |
| `UC02_Diem_danh_QR.md` | Spec UC02 |
| `docs/messages.md` + `lang/*/messages.php` | MSG-01…05 |
| Models / migrations / enums | Schema sẵn |
| `.cursor/rules/cca-qr-attendance.mdc` | Rule chống lệch hướng |
| Wiki `Use-Cases/` | Spec đầy đủ hơn |

## Prompt gợi ý khi demo

```text
Đọc docs/use-cases/UC01 và UC02 + .cursor/rules/cca-qr-attendance.mdc
rồi triển khai lại đúng source-of-truth trong các file đó
(API + Service + Repository + UI admin sessions/live + public check-in + tests).
```
