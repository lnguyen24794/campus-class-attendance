# Class Repository

## Quy tắc chung

- Tên class: `{Noun}Repository` (ví dụ `AttendanceRepository`) — **không** prefix `Eloquent`
- Một repository gắn một aggregate/bảng chính; được phép gom bảng liên quan chặt
- Đây là **tầng duy nhất** được truy cập Eloquent / CSDL

## Đặt tên method

Format: `{Verb}{Target}` theo home.md.


| Động từ | Ý nghĩa | Ví dụ CCA |
|---------|---------|-----------|
| `find` | Một bản ghi | `findAttendanceByStudentAndSession`, `findUserByEmailOrCode` |
| `search` | Nhiều bản ghi | `searchAttendances`, `searchSubjects`, `searchUsersByRole` |
| `list` | Collection không paginate | `listPeriods`, `listAttendancesBySession` |
| `insert` | Thêm | `insertAttendance`, `insertSubject` |
| `update` | Cập nhật | `updateSubject`, `updateUser` |
| `delete` | Xóa | `deleteSubject`, `deleteUser` |
| `exists` | Tồn tại | `existsEnrollment` |




## Query

- Ưu tiên Eloquent Model
- Query Builder chỉ khi cần hiệu năng / SQL Eloquent không xây được sạch
- Điều kiện `where` dùng chung → tách method private/protected tái sử dụng



## Ví dụ (CCA)

```php
class AttendanceRepository
{
    public function searchAttendances(int $studentId, int $limit): LengthAwarePaginator
    {
        return Attendance::query()
            ->with(['courseSection', 'attendanceSession'])
            ->where('student_id', $studentId)
            ->orderByDesc('checked_in_at')
            ->paginate($limit);
    }

    public function findAttendanceByStudentAndSession(int $studentId, string $sessionId): ?Attendance
    {
        return Attendance::query()
            ->where('student_id', $studentId)
            ->where('attendance_session_id', $sessionId)
            ->first();
    }

    public function insertAttendance(array $attributes): Attendance
    {
        return Attendance::query()->create($attributes);
    }
}
```



## Cấm

- Repository gọi Service / Controller
- Nhúng logic nghiệp vụ phức tạp (quy tắc QR hết hạn, MSG…) — để ở Service

