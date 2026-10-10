# Class Request (Form Request)

## Phân tầng

```
FormRequest (Laravel)
  → BaseFormRequest     # snake_case keys + cast sau validate
    → BaseRequest       # hằng REQUIRED/NULLABLE + mergeCommonRules()
      → Store* / CheckIn* / Update*
    → BaseListRequest   # cast query + bỏ empty
      → Index*
  → Common/CommonRules  # type / format / unique / exists (tái sử dụng)
```

| Tầng | Trách nhiệm |
|------|-------------|
| `BaseFormRequest` | camelCase → snake_case; `$casts` |
| `BaseRequest` | Hằng `REQUIRED` / `NULLABLE`; `mergeCommonRules()`; enforce presence-only |
| `BaseListRequest` | Query list: cast `limit`, loại bỏ giá trị rỗng |
| `Common/CommonRules` | **Một nơi** khai báo rule theo tên field (string, max, unique, exists…) |
| Request API | **Chỉ** `[self::REQUIRED]` hoặc `[self::NULLABLE]` (tuỳ field) |

## Cấu trúc thư mục

```
app/Http/Requests/
├── BaseFormRequest.php
├── BaseRequest.php
├── BaseListRequest.php
├── Common/
│   └── CommonRules.php          ← thêm field chung tại đây
└── Api/V1/
    ├── Admin/          # Store/Update master + session
    ├── Students/
    └── Lecturers/
```

Cursor rule: `campus-class-attendance/.cursor/rules/laravel-form-request.mdc`


## Quy tắc bắt buộc (DoD khi viết Request)

1. Field mới dùng lại → thêm vào `CommonRules::definitions()`.
2. File Request API **không** được viết `string`, `max:`, `email`, `Rule::unique`, `exists:`…
3. Trong `rules()` chỉ còn:

```php
return $this->mergeCommonRules([
    'email' => [self::REQUIRED],
    'password' => [self::NULLABLE],
]);
```

4. `mergeCommonRules()` sẽ gắn `bail` + presence + common. Nếu API Request khai báo token khác presence → ném `InvalidArgumentException` (chặn regression).
5. `authorize()` mặc định `true` ở base (role đã chặn middleware).
6. Controller chỉ gọi `$request->validated()`.

## Ví dụ đúng

```php
class StoreMasterUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'name' => [self::REQUIRED],
            'email' => [self::REQUIRED],
            'code' => [self::REQUIRED],
            'password' => [self::REQUIRED],
        ]);
    }
}
```

## Ví dụ sai (không làm)

```php
// ❌ Lặp type/unique trong API Request
'email' => [self::REQUIRED, self::EMAIL, Rule::unique('users', 'email')],
```

## Field `code` (users vs subjects)

`CommonRules` chọn bảng unique theo context:

- `StoreSubjectRequest` / `UpdateSubjectRequest` / route `subject` → `subjects.code`
- Còn lại (student/lecturer) → `users.code`

`Rule::unique(...)->ignore($routeModelId)` dùng chung cho create (id = null) và update.

## Thêm field chung — checklist

1. Thêm key vào `CommonRules::definitions()`
2. API Request chỉ thêm `'field' => [self::REQUIRED|NULLABLE]`
3. Cập nhật ví dụ trong wiki này nếu field quan trọng
4. Feature test happy + validation fail (nếu đổi contract)

## Cursor / AI Agents

Rule dự án: `.cursor/rules/laravel-form-request.mdc` — luôn áp dụng khi sửa Form Request.
