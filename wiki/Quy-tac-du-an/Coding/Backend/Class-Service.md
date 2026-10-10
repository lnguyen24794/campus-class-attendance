# Class Service

## Quy tắc chung

- Tên class: `{Noun}Service` — `AttendanceService`, `AttendanceSessionService`, `MasterUserService`, `SubjectService`, `PeriodService`, `PublicUrlService`
- Chỉ inject Repository (+ `DB` khi cần transaction)
- **Không** nhận `Request` — nhận `User` + `array $params` từ `$request->validated()`
- **Không** gọi `Model::query()` trực tiếp (ưu tiên Repository)
- Trả **Model** (hoặc Paginator) để Controller bọc Resource — tránh trả array thô khi có Resource
- Sau check-in thành công: dispatch `AttendanceCheckedIn` (Reverb)

## Đặt tên method (home.md)

| Động từ | Ví dụ CCA |
|---------|-----------|
| `fetch…List` | `fetchAttendanceList`, `fetchSubjectList`, `fetchUserList` |
| `create` | `createAttendance`, `createAttendanceSession`, `createSubject` |
| `update` / `delete` | `updateSubject`, `deleteUser` |
| Khác | `refreshQrToken`, `baseUrl` / `checkInUrl` (`PublicUrlService`) |

## PHPDoc chuẩn

```php
/**
 * Create an attendance record via QR check-in.
 *
 * @param  User  $student  Authenticated student
 * @param  array{session_qr_token: string}  $params  Validated body parameters
 *
 * @throws AttendanceException
 */
public function createAttendance(User $student, array $params): Attendance
```

## Ví dụ

```php
public function fetchAttendanceList(User $student, array $params): LengthAwarePaginator
{
    $limit = (int) ($params['limit'] ?? 20);

    return $this->attendanceRepository->searchAttendances($student->id, $limit);
}
```

## Ngoại lệ nghiệp vụ

- Throw `AttendanceException` / `ModelNotFoundException`
- **Không** `return response()->json(...)` từ Service
