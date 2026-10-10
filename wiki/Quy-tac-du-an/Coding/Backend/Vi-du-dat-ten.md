# Ví dụ đặt tên điển hình

| Màn / UC | HTTP | Endpoint | Controller | Service | Repository | Resource |
|----------|------|----------|------------|---------|------------|----------|
| UC01 List | GET | `/api/v1/students/attendances` | `index` | `fetchAttendanceList` | `searchAttendances` | `AttendanceResourceForList` |
| UC02 Check-in | POST | `/api/v1/students/attendances/check-in` | `checkIn` | `createAttendance` | `insertAttendance`… | `AttendanceResource` + `ApiResponse::created` |
| Mở buổi | POST | `/api/v1/lecturers/attendance-sessions` | `store` | `createAttendanceSession` | `insertAttendanceSession` | `AttendanceSessionResource` + `ApiResponse::created` |

## Resource

| Số lượng | Class |
|----------|-------|
| Nhiều (list) | `{Noun}ResourceForList` |
| Một (detail/create) | `{Noun}Resource` |

## Request

| HTTP | Base | Class ví dụ |
|------|------|-------------|
| GET list | `BaseListRequest` | `IndexStudentAttendanceRequest` |
| POST body | `BaseRequest` | `StoreAttendanceSessionRequest`, `CheckInAttendanceRequest` |
