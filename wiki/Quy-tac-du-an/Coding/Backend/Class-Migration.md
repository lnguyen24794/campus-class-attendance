# Class Migration

## Đặt tên file

### Thêm bảng

```
create_{table_name}_table
```

Ví dụ trong CCA:

- `create_course_sections_table`
- `create_enrollments_table`
- `create_attendance_sessions_table`
- `create_attendances_table`
- `create_subjects_table` / `create_periods_table` (master data)
- `add_code_to_users_table`
- `add_subject_and_period_fks` (`course_sections.subject_id`, `attendance_sessions.period_id`)

DB app mặc định: **PostgreSQL**; phpunit: SQLite memory.

```bash
php artisan make:migration create_attendances_table
```

### Sửa bảng

| Thao tác | Naming |
|----------|--------|
| Thêm cột | `add_{column}_to_{table}` |
| Xóa cột | `remove_{column}_from_{table}` |
| Đổi tên cột | `rename_{old}_to_{new}_in_{table}` |
| Đổi kiểu | `change_{column}_to_{type}_in_{table}` |
| Index / unique / FK | `add_index_to_{table}`, `add_unique_key_to_{table}`, … |
| Nhiều thay đổi | `modify_{table}` |

Ví dụ: `add_role_to_users_table`

## Thiết kế bảng (CCA)

- Khóa chính: `id` (bigint hoặc UUID tùy bảng)
- Không dùng composite primary key
- Timestamp: `created_at` / `updated_at` theo mặc định Laravel khi phù hợp
- Soft-delete / audit cột (`is_deleted`, `created_by`…) **không bắt buộc** trong demo — chỉ thêm khi có yêu cầu rõ

## Unique nghiệp vụ

- Điểm danh một lần / buổi: dùng `unique_key` (hoặc unique index trên `(student_id, attendance_session_id)`) để chống trùng

## Chạy migration

```bash
php artisan migrate
php artisan migrate:fresh   # dev/demo — xóa sạch rồi tạo lại
```
