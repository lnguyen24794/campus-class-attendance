# PHPDoc chuẩn

Áp dụng cho **mọi method public** (và private quan trọng) trên Controller, Service, Repository, Request, Resource, ApiResponse.

## Mục tiêu

- Đọc nhanh trách nhiệm method
- Refer được từ wiki / IDE / AI agent
- Không thay thế type hint PHP — bổ sung ngữ cảnh nghiệp vụ

## Mẫu tối thiểu

```php
/**
 * {Một câu mô tả hành vi bằng tiếng Anh.}
 *
 * @param  Type  $name  Ý nghĩa nghiệp vụ (khi cần)
 * @return Type  Ý nghĩa (khi return type chưa đủ rõ)
 * @throws SomeException  Khi nào throw
 */
```

## Theo tầng

| Tầng | Ghi chú PHPDoc |
|------|----------------|
| Controller | Mô tả HTTP action; `@param` Request |
| Service | Use case; `@param` User + `array{...}` validated shape; `@throws` |
| Repository | Truy vấn/ghi; `@param` id / attributes |
| Request `rules()` | `@return array<string, list<mixed>>` |
| Resource `toArray()` | List vs detail; `@return array<string, mixed>` |
| ApiResponse | Mô tả status / envelope |

## Ví dụ Service

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

## Lưu ý

- Nội dung wiki / comment nghiệp vụ cho người: **tiếng Việt**
- PHPDoc trong code: **tiếng Anh** (đồng bộ codebase hiện tại)
- Tránh PHPDoc trùng 100% với signature mà không thêm thông tin (Pint có thể bỏ tag thừa)
