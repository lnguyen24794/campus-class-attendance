# Quy tắc coding Laravel (CCA)

Tuân thủ coding style của Laravel và PSR-4 autoloading.

## Định dạng code

- Dùng **Laravel Pint** (`vendor/bin/pint`)
- Format trước khi commit / tạo PR
- Chi tiết: [Định dạng code](Dinh-dang-code)

## Quy ước đặt tên

### Kiểu chữ

| Đối tượng | Style |
|-----------|-------|
| Class | UpperCamelCase (PascalCase) |
| Method / biến | lowerCamelCase |
| Field API request/response | snake_case |
| Bảng DB | snake_case, số nhiều |
| Cột DB | snake_case |

### Class
- Danh từ, file `{ClassName}.php`
- Ví dụ: `AttendanceService`, `AttendanceRepository`

### Method
- `verb + noun` (lowerCamelCase)
- Ví dụ: `fetchAttendanceList()`, `createAttendance()`, `createAttendanceSession()`

### Biến
- Chủ yếu danh từ
- Boolean: `is` / `has` / `can` + noun → `isOpen`, `hasCheckedIn`
- Collection / mảng: số nhiều

### Endpoint API
- Danh từ số nhiều cho resource: `attendances`, `attendance-sessions`
- Nhiều từ: kebab-case
- Thao tác ngoài CRUD: `POST` + `/{verb}` → ví dụ `attendances/check-in`

## Type hinting

- Luôn khai báo kiểu tham số và return type
- Tránh `mixed` khi có thể
- Property constructor promotion được khuyến khích (PHP 8+)

## Kiến trúc phân tầng

| Tầng | Trách nhiệm |
|------|-------------|
| **Controller** | Nhận request, gọi Service, trả Resource / JSON |
| **Service** | Logic nghiệp vụ (use case) |
| **Repository** | Truy cập Eloquent / CSDL — **tầng duy nhất** được query DB |

Quy tắc bắt buộc:
1. Chỉ Repository được gọi Model / query DB
2. Tầng dưới không gọi tầng trên (Repository không gọi Service)
3. Service **không** nhận `Request` object — chỉ nhận dữ liệu đã validate / entity
4. Controller **không** gọi Repository trực tiếp

```
Yêu cầu HTTP
  → Form Request (validate)
  → Controller
  → Service
  → Repository
  → Model / CSDL
```

## Form Request

- Rule type/format/unique/exists: `app/Http/Requests/Common/{Module}Rules.php` (theo module)
- FormRequest API: folder `Api/V1/{Module}/` — chỉ `REQUIRED` / `NULLABLE` + `moduleCommonRules()` + `mergeCommonRules()`
- Chi tiết: [Class Request](Class-Request)

## Ngày giờ

- Trong domain/service dùng Carbon / `now()` của Laravel là chấp nhận được (demo)
- Response API: ISO 8601 qua `toAtomString()` / `\DateTimeInterface::ATOM`

## Transaction

- Đặt trong **Service**, dùng `DB::transaction(function () { ... })`
- Chi tiết: [Transaction](Transaction)

## Biến môi trường

- Chỉ dùng `env()` trong file `config/`
- Trong app dùng `config('...')`

## Khác

- Khi tham chiếu class dùng `Attendance::class`, không dùng chuỗi `'App\Models\Attendance'`
- Method không tầm thường → PHPDoc tiếng Anh (theo convention code hiện tại)
- Message người dùng: qua `__('messages.MSG-xx')` và `lang/vi/messages.php`
