# Class Controller

## Quy tắc chung

- Thư mục khớp routing / nhóm API:
  - `/api/v1/students/...` → `Api\V1\Students\...`
  - `/api/v1/lecturers/...` → `Api\V1\Lecturers\...`
  - `/api/v1/admin/...` → `Api\V1\Admin\...`
  - `/api/v1/public/...` → `Api\V1\Public\...`
  - Blade shells → `Web\...` (chỉ `view()`, không business)
- Tên class: `{SingularNoun}Controller`
- **Chỉ** inject Service — không gọi Repository (trừ chỗ đã thống nhất trong Admin options/live nếu cần repository đọc list — ưu tiên Service)
- Action REST: `index`, `store`, `show`, `update`, `destroy`
- Non-CRUD: verb trên URL → method cùng tên (`checkIn`, `live`, `refreshQr`)

## Pattern API

```
index  → Service::fetch…List(...)  → items + meta / ResourceForList
store  → Service::create…(...)     → ApiResponse::created(Resource)
update → Service::update…(...)     → ApiResponse::success(Resource)
destroy→ Service::delete…(...)     → ApiResponse::success(null, …)
```

- Truyền **`$request->validated()`** nguyên khối
- POST create → HTTP **201**

## Nhóm Controller đã có

| Nhóm | Ví dụ |
|------|--------|
| Students | `StudentAttendanceController` |
| Lecturers | `AttendanceSessionController` |
| Admin | `StudentController`, `LecturerController`, `SubjectController`, `PeriodController`, `AttendanceSessionAdminController`, `DashboardController` |
| Public | `PublicCheckInController` |
| Web | `AuthController`, `DashboardController`, `AttendanceSessionLiveController`, `PublicCheckInController` (Blade shell) |

## PHPDoc chuẩn

```php
/**
 * Display a listing of attendances for the authenticated student.
 *
 * @param  IndexStudentAttendanceRequest  $request  Validated list query
 */
public function index(IndexStudentAttendanceRequest $request): JsonResponse
```

## Cấm

- Validate thủ công trong Controller khi đã có FormRequest
- Gọi `Model::query()` phức tạp trong Controller
- Trả Blade từ API Controller (và ngược lại: Web Controller không trả business JSON — JSON đi API)
