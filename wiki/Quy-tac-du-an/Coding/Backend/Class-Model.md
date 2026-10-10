# Class Model

## Quy tắc chung

- Extends `\Illuminate\Database\Eloquent\Model` (hoặc `Authenticatable` với `User`)
- Tên class = tên bảng dạng singular PascalCase
- UUID: trait `HasUuids` khi khóa chính là UUID

## Thực thể CCA hiện tại

| Model | PK | Ghi chú |
|-------|----|---------|
| `User` | id | `role`: student / lecturer / **admin**; thêm `code` (MSSV/MGV) |
| `Subject` | uuid | Môn học master |
| `Period` | uuid | Tiết học (`number` unique, `starts_at`/`ends_at`) |
| `CourseSection` | uuid | Lớp HP; FK `subject_id`, `lecturer_id` |
| `Enrollment` | id | SV ↔ lớp |
| `AttendanceSession` | uuid | Buổi + QR; FK `period_id` nullable |
| `Attendance` | uuid | Bản ghi điểm danh |

## Thuộc tính

- Khai báo `$fillable` rõ ràng
- Cast qua method `casts()`:

```php
protected function casts(): array
{
    return [
        'status' => AttendanceStatus::class,
        'checked_in_at' => 'datetime',
        'role' => UserRole::class,
        'password' => 'hashed',
    ];
}
```

## Enum

- `AttendanceStatus` — present / late
- `SessionStatus` — open / …
- `UserRole` — student / lecturer / **admin**

## Quan hệ (Relations)

- 1:1 / N:1 → singular: `student()`, `courseSection()`, `subject()`, `period()`
- 1:N → plural: `attendances()`, `courseSections()`

## Phương thức domain trên Model

- Helper state nhỏ: `isQrExpired()`, `isOpen()`, `isStudent()`, `isLecturer()`, `isAdmin()`
- Logic use case nhiều bước → Service

## Cấm

- Controller/Service gọi query phức tạp trực tiếp trên Model — đi qua Repository
