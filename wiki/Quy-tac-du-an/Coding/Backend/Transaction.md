# Transaction

## Nguyên tắc

- Transaction đặt ở **Service layer**
- Ưu tiên `DB::transaction()` (closure)

```php
$attendance = DB::transaction(function () use ($student, $session, $status): Attendance {
    return $this->attendanceRepository->create([
        'student_id' => $student->id,
        'attendance_session_id' => $session->id,
        // ...
    ]);
});
```

## Tránh

- `beginTransaction` / `commit` / `rollback` thủ công trừ khi cần kiểm soát rollback rất đặc thù
- Lồng transaction nhiều tầng trong code (Laravel chỉ áp dụng outer nhất — nhưng code khó đọc)

## Khi nào dùng

- Ghi nhiều bảng liên quan trong một use case
- Cần atomic: hoặc tất cả thành công, hoặc không ghi gì (ví dụ tạo attendance + cập nhật thống kê sau này)
