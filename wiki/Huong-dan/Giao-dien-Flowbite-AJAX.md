# Giao diện Flowbite & AJAX

UI admin theo Flowbite + token HT Plus (admin-frontend): sidebar, CRUD, search collapse, pagination footer.

## Nguyên tắc

| Lớp | Vai trò |
|-----|---------|
| Blade | Shell — **không** submit CRUD form truyền thống |
| JS | `fetch` → `/api/v1/admin/*` (CSRF + cookie) |
| API Admin | JSON `ApiResponse` = nguồn sự thật |

## Trang Blade ↔ API

| Route web | `data-page` | JS | API |
|-----------|-------------|----|-----|
| `/dashboard` | `dashboard` | `initDashboardPage` | `GET /admin/dashboard` |
| `/students` | `students` | `initCrudPage` | CRUD `/admin/students` |
| `/lecturers` | `lecturers` | `initCrudPage` | CRUD `/admin/lecturers` |
| `/subjects` | `subjects` | `initCrudPage` | CRUD `/admin/subjects` |
| `/periods` | `periods` | `initCrudPage` | CRUD `/admin/periods` |
| `/sessions` | `sessions-history` | `initSessionsHistoryPage` | `GET /admin/attendance-sessions` |
| `/sessions/create` | `sessions-create` | `initSessionCreatePage` | options + `POST …/attendance-sessions` |
| `/sessions/{id}/live` | `sessions-live` | `initSessionLivePage` | live + refresh-qr + Echo |
| `/check-in/{token}` | `check-in` | `initCheckInPage` | `/public/check-in/*` |

## UI rules (khớp admin-frontend / source)

| Hạng mục | Quy tắc |
|----------|---------|
| Theme | **Light only** |
| Brand sidebar | Cùng chiều cao header (`h-14`), brand `text-base` |
| Search CRUD | **Collapse mặc định** (`#crud-search-panel.hidden`) |
| Search sessions | Collapse mặc định tương tự |
| Pagination | Phải: «Số mục mỗi trang» `10/20/50` + `from-to của total` + first/prev/next/last icon |
| List actions | Icon + chữ; gray → primary/red hover (`ht-action-btn`) |
| Status chips | Outlined; **Đúng giờ** / **Đi muộn** / **Đang mở** / **Đã đóng** |
| Live | Không Public URL; QR + link + Sao chép; meta Kết thúc + Đúng giờ ≤ N phút |

## File quan trọng

```
resources/views/layouts/app.blade.php
resources/views/components/crud-page.blade.php
resources/views/sessions/index.blade.php
resources/views/sessions/create.blade.php
resources/views/sessions/live.blade.php
resources/js/api.js
resources/js/pages/crud-resource.js
resources/js/pages/sessions-history.js
resources/js/pages/sessions.js
resources/js/ui/pagination.js
resources/css/app.css
```

## Auth web

- Session + CSRF `X-CSRF-TOKEN`
- Role: `admin` hoặc `lecturer`

## Không làm

- Không nhúng nghiệp vụ vào Blade
- Không hiện Public URL trên Live
- Không pagination kiểu số trang (1 2 3…) thay cho footer admin-frontend
- Không để route API `apiResource` đè tên Blade (luôn `->names('admin.*')`)
