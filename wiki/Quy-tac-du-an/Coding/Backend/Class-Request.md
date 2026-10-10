# Class Request (Form Request)

## Phân tầng

```
FormRequest (Laravel)
  → BaseFormRequest     # snake_case keys + cast sau validate
    → BaseRequest       # hằng REQUIRED/NULLABLE + mergeCommonRules() + moduleCommonRules()
      → Store* / Update* / CheckIn*
    → BaseListRequest   # ListRules + listPresenceRules() + listFilters()
      → Index*
  → Common/{Module}Rules  # type / format / unique / exists theo module
```

| Tầng | Trách nhiệm |
|------|-------------|
| `BaseFormRequest` | camelCase → snake_case; `$casts` |
| `BaseRequest` | Hằng `REQUIRED` / `NULLABLE`; `mergeCommonRules()`; `moduleCommonRules()`; enforce presence-only |
| `BaseListRequest` | Query list: `ListRules`, cast `limit`, loại bỏ giá trị rỗng, `listFilters()` |
| `Common/{Module}Rules` | Rule type/format/unique/exists **theo module** (`UserRules`, `SubjectRules`, …) |
| Request API | **Chỉ** `[self::REQUIRED]` hoặc `[self::NULLABLE]` (tuỳ field) |

## Cấu trúc thư mục

```
app/Http/Requests/
├── BaseFormRequest.php
├── BaseRequest.php
├── BaseListRequest.php
├── Common/
│   ├── ListRules.php            ← q, page, limit, per_page, status
│   ├── UserRules.php            ← student / lecturer
│   ├── SubjectRules.php
│   ├── PeriodRules.php
│   └── AttendanceRules.php      ← UC01 / UC02 (session + check-in)
└── Api/V1/
    ├── Students/                ← Index / Store / Update StudentRequest
    ├── Lecturers/
    ├── Subjects/
    ├── Periods/
    └── …                        ← module mới = folder mới (không nhét vào Admin/)
```

Cursor rule: `campus-class-attendance/.cursor/rules/laravel-form-request.mdc`

## Quy tắc bắt buộc (DoD khi viết Request)

1. Field mới → thêm vào đúng `Common/{Module}Rules::definitions()`, **không** gom hết vào một god-class.
2. File Request API đặt trong folder module (`Api/V1/{Module}/`), đặt tên `Index|Store|Update{Noun}Request`.
3. Override `moduleCommonRules()` trả về Rules của module đó.
4. File Request API **không** được viết `string`, `max:`, `email`, `Rule::unique`, `exists:`…
5. Trong `rules()` chỉ còn:

```php
return $this->mergeCommonRules([
    'email' => [self::REQUIRED],
    'password' => [self::NULLABLE],
]);
```

6. `mergeCommonRules()` gắn `bail` + presence + module rules. Token khác presence → `InvalidArgumentException`.
7. `authorize()` mặc định `true` ở base (role đã chặn middleware).
8. Controller chỉ gọi `$request->validated()` / `$request->listFilters()`.

## Ví dụ đúng

```php
namespace App\Http\Requests\Api\V1\Students;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Common\UserRules;

class StoreStudentRequest extends BaseRequest
{
    protected function moduleCommonRules(): array
    {
        return UserRules::definitions($this);
    }

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
// ❌ Gom mọi Request vào Api/V1/Admin/
// ❌ Một CommonRules chứa field của mọi module
// ❌ Lặp type/unique trong API Request
'email' => [self::REQUIRED, self::EMAIL, Rule::unique('users', 'email')],
```

## Field `code` (users vs subjects)

- Student / Lecturer → `UserRules` → `users.code`
- Subject → `SubjectRules` → `subjects.code`

`Rule::unique(...)->ignore($routeModelId)` dùng chung cho create (id = null) và update.

## Thêm field / module — checklist

1. Field thuộc module nào → sửa `{Module}Rules`
2. API Request trong folder module: chỉ `'field' => [self::REQUIRED|NULLABLE]`
3. Module mới (vd. AttendanceSessions): tạo folder `Api/V1/AttendanceSessions/` + `AttendanceRules` (đã có sẵn)
4. Feature test happy + validation fail (nếu đổi contract)

## Cursor / AI Agents

Rule dự án: `.cursor/rules/laravel-form-request.mdc` — áp dụng khi sửa Form Request.
