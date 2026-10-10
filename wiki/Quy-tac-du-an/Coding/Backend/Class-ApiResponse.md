# Class ApiResponse

Helper envelope JSON dùng chung cho API (không `response()->json([...])` rải trong Controller).

## Vị trí

`App\Http\Response\ApiResponse`

## Envelope

### Thành công

```json
{
  "success": true,
  "data": {},
  "message": "Success"
}
```

### Thất bại

```json
{
  "success": false,
  "message": "Error",
  "errors": null
}
```

## Method

| Method | Status | Khi nào |
|--------|--------|---------|
| `success($data, $message, $code)` | mặc định 200 | GET detail, PUT |
| `created($data, $message)` | **201** | POST create |
| `error` / `notFound` / `forbidden` / `unauthorized` | 4xx | Lỗi chủ động (hiếm — ưu tiên Exception) |
| `validationError($errors)` | 422 | Validation (thường Laravel tự xử lý) |

## PHPDoc chuẩn

```php
/**
 * Build a 201 Created JSON response.
 *
 * @param  mixed  $data  Payload (Resource, array, or scalar)
 */
public static function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
```

## Controller

```php
// List — Resource collection (Laravel pagination meta), không bắt buộc bọc ApiResponse
return AttendanceResourceForList::collection($attendances);

// Create
return ApiResponse::created(new AttendanceResource($attendance), $message);
```

## Lỗi nghiệp vụ

`AttendanceException::render()` trả envelope tương thích (`success: false`, `message`, `code`).
