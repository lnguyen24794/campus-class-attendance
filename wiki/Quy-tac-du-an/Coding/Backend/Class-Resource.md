# Class Resource

## Quy tắc chung

- Thư mục khớp routing / resource:
  - `/api/v1/students/attendances` → `App\Http\Resources\Api\V1\Attendances\`
  - `/api/v1/lecturers/attendance-sessions` → `App\Http\Resources\Api\V1\AttendanceSessions\`
- **Cấm** `$model->toArray()` — dễ lộ field nhạy cảm
- **Cấm** logic phức tạp / gọi DB / gọi Repository trong Resource
- Vòng lặp đơn giản được phép
- Ngày giờ: `toAtomString()` (ISO 8601 / ATOM)
- Nesting: Resource riêng hoặc map field tường minh; eager load ở Repository/Service

## Đặt tên: số ít vs số nhiều

| Loại | Tên class | Khi nào dùng |
|------|-----------|--------------|
| **Số ít (detail / create / update)** | `{Noun}Resource` | `show`, `store`, `update`, check-in trả 1 bản ghi |
| **Số nhiều (list)** | `{Noun}ResourceForList` | `index` / collection / paginator |

Ví dụ CCA:

| Endpoint | Resource |
|----------|----------|
| `GET …/attendances` | `AttendanceResourceForList::collection(...)` |
| `POST …/attendances/check-in` | `new AttendanceResource($attendance)` |
| `POST …/attendance-sessions` | `new AttendanceSessionResource($session)` |

> `ResourceForList` thường nhẹ hơn (ít field / ít nest). `Resource` dùng cho chi tiết hoặc payload sau create.

## PHPDoc chuẩn

```php
/**
 * Transform the attendance model for list/collection responses.
 *
 * @return array<string, mixed>
 */
public function toArray(Request $request): array
```

## Ví dụ

```php
// List
return AttendanceResourceForList::collection($attendances);

// Create / detail
return ApiResponse::created(new AttendanceResource($attendance), $message);
```
