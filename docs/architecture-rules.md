# Quy ước kiến trúc — Demo điểm danh QR

Theo `imatomi_na-admin.wiki/home.md` + mẫu `ApiResponse` / Base Request / Resource:

- Controller: mỏng; `validated()` → Service; list = `ResourceForList`; create = `ApiResponse::created(Resource)`
- Service: `{Noun}Service`; `fetch*` / `create*`; nhận `User` + `array $params`
- Repository: `{Noun}Repository`; `find` / `search` / `insert`
- Request: folder theo module (`Api/V1/Students|Lecturers|Subjects|Periods/…`); type/unique/exists trong `Common\{Module}Rules` + `moduleCommonRules()`; API Request chỉ `REQUIRED`/`NULLABLE` qua `mergeCommonRules()`
- Resource: `{Noun}Resource` (số ít) · `{Noun}ResourceForList` (số nhiều)
- POST create → **201**
- Method public → PHPDoc tiếng Anh
- Thời gian API: ISO 8601 (`toAtomString()`)

## Luồng

```
HTTP
  → Form Request (Base*)
  → Controller
  → Service
  → Repository
  → Model / CSDL
```

## Ánh xạ

| Endpoint | Controller | Service | Resource |
|----------|------------|---------|----------|
| GET …/attendances | `index` | `fetchAttendanceList` | `AttendanceResourceForList` |
| POST …/check-in | `checkIn` | `createAttendance` | `AttendanceResource` + ApiResponse |
| POST …/attendance-sessions | `store` | `createAttendanceSession` | `AttendanceSessionResource` + ApiResponse |
