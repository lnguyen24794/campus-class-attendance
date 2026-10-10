<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed realistic master data for Khoa CNTT — Trường ĐH Bách khoa Đà Nẵng (DUT).
 *
 * Tham chiếu ngành/mã:
 * - 7480201  Công nghệ thông tin
 * - 7480201A Công nghệ thông tin (ngoại ngữ Nhật)
 * - 7480201B CNTT — chuyên ngành Khoa học dữ liệu & Trí tuệ nhân tạo
 *
 * @see https://dut.udn.vn/KhoaCNTT/Gioithieu
 */
class DutCnttSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const SEMESTER = '2025-2026.1';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPeriods();
        $admin = $this->seedAdmin();
        $lecturers = $this->seedLecturers();
        $subjects = $this->seedSubjects();
        $students = $this->seedStudents();
        $sections = $this->seedCourseSections($subjects, $lecturers);
        $this->seedEnrollments($sections, $students);

        $this->command?->info('DUT CNTT seeder xong.');
        $this->command?->table(
            ['Loại', 'Số lượng / ghi chú'],
            [
                ['Admin', $admin->email.' / '.self::PASSWORD],
                ['Giảng viên', (string) count($lecturers)],
                ['Sinh viên', (string) count($students)],
                ['Môn học', (string) count($subjects)],
                ['Lớp học phần', (string) count($sections)],
                ['Học kỳ', self::SEMESTER],
            ],
        );
    }

    private function seedPeriods(): void
    {
        $slots = [
            [1, 'Tiết 1', '07:00', '07:50'],
            [2, 'Tiết 2', '08:00', '08:50'],
            [3, 'Tiết 3', '09:00', '09:50'],
            [4, 'Tiết 4', '10:00', '10:50'],
            [5, 'Tiết 5', '11:00', '11:50'],
            [6, 'Tiết 6', '12:30', '13:20'],
            [7, 'Tiết 7', '13:30', '14:20'],
            [8, 'Tiết 8', '14:30', '15:20'],
            [9, 'Tiết 9', '15:30', '16:20'],
            [10, 'Tiết 10', '16:30', '17:20'],
            [11, 'Tiết 11', '17:30', '18:20'],
            [12, 'Tiết 12', '18:30', '19:20'],
        ];

        foreach ($slots as [$number, $name, $starts, $ends]) {
            Period::query()->updateOrCreate(
                ['number' => $number],
                [
                    'name' => $name,
                    'starts_at' => $starts,
                    'ends_at' => $ends,
                ],
            );
        }
    }

    private function seedAdmin(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'admin@dut.udn.vn'],
            [
                'name' => 'Quản trị Khoa CNTT — DUT',
                'code' => 'DUT-ADM',
                'role' => UserRole::Admin,
                'password' => self::PASSWORD,
            ],
        );
    }

    /**
     * @return array<string, User>
     */
    private function seedLecturers(): array
    {
        $rows = [
            ['GV001', 'TS. Nguyễn Văn An', 'nguyenvanan@dut.udn.vn'],
            ['GV002', 'PGS.TS. Trần Thị Bình', 'tranthibinh@dut.udn.vn'],
            ['GV003', 'TS. Lê Minh Cường', 'leminhcuong@dut.udn.vn'],
            ['GV004', 'ThS. Phạm Hoàng Dũng', 'phamhoangdung@dut.udn.vn'],
            ['GV005', 'TS. Hoàng Thị Em', 'hoangthiem@dut.udn.vn'],
            ['GV006', 'ThS. Võ Quốc Phong', 'voquocphong@dut.udn.vn'],
            ['GV007', 'TS. Đặng Thu Hà', 'dangthuha@dut.udn.vn'],
            ['GV008', 'ThS. Bùi Đức Khoa', 'buiduckhoa@dut.udn.vn'],
            // Alias ổn định cho demo seminar / README
            ['GV-DEMO', 'GV Demo CCA', 'lecturer@cca.test'],
        ];

        $lecturers = [];

        foreach ($rows as [$code, $name, $email]) {
            $lecturers[$code] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'code' => $code,
                    'role' => UserRole::Lecturer,
                    'password' => self::PASSWORD,
                ],
            );
        }

        return $lecturers;
    }

    /**
     * @return array<string, Subject>
     */
    private function seedSubjects(): array
    {
        $rows = [
            // Khối kiến thức cơ sở ngành CNTT (DUT)
            ['IT001', 'Nhập môn lập trình', 3, 'Ngôn ngữ C/C++, tư duy thuật toán — Khoa CNTT DUT'],
            ['IT002', 'Cấu trúc dữ liệu và giải thuật', 3, 'Danh sách, cây, đồ thị, độ phức tạp'],
            ['IT003', 'Cơ sở dữ liệu', 3, 'Mô hình quan hệ, SQL, chuẩn hóa'],
            ['IT004', 'Hệ điều hành', 3, 'Tiến trình, bộ nhớ, hệ thống tệp'],
            ['IT005', 'Mạng máy tính', 3, 'TCP/IP, định tuyến, phòng lab mạng DUT'],
            ['IT006', 'Lập trình hướng đối tượng', 3, 'Java/C++, UML, design patterns'],
            ['IT007', 'Công nghệ phần mềm', 3, 'Quy trình phát triển, yêu cầu, kiểm thử'],
            ['IT008', 'Lập trình Web', 3, 'HTML/CSS/JS, Laravel/Node — demo điểm danh QR'],
            ['IT009', 'An toàn thông tin', 3, 'Mã hóa, xác thực, bảo mật ứng dụng web'],
            ['IT010', 'Trí tuệ nhân tạo', 3, 'Search, knowledge, intro ML — ngành 7480201B'],
            ['IT011', 'Học máy', 3, 'Supervised/unsupervised learning, đánh giá mô hình'],
            ['IT012', 'Khoa học dữ liệu', 3, 'ETL, phân tích, trực quan hóa dữ liệu'],
            ['IT013', 'Hệ thống thông tin quản lý', 3, 'Phân tích nghiệp vụ, ERP'],
            ['IT014', 'Kiến trúc máy tính', 2, 'CPU, bộ nhớ, pipeline'],
            ['IT015', 'Thực tập doanh nghiệp', 2, 'Hợp tác doanh nghiệp — chương trình đặc thù DUT'],
            // Alias demo ổn định
            ['CT101', 'Lập trình Web (nhóm demo seminar)', 3, 'Môn demo CCA — map IT008'],
        ];

        $subjects = [];

        foreach ($rows as [$code, $name, $credits, $description]) {
            $subjects[$code] = Subject::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'credits' => $credits,
                    'description' => $description,
                ],
            );
        }

        return $subjects;
    }

    /**
     * @return list<User>
     */
    private function seedStudents(): array
    {
        // MSSV kiểu DUT: năm nhập học + mã khoa/ngành + số thứ tự
        $cohorts = [
            // Khóa 21 — CNTT (7480201)
            ['102210001', 'Nguyễn Minh Tuấn', '102210001@sv.dut.udn.vn', '7480201'],
            ['102210015', 'Trần Thị Lan Anh', '102210015@sv.dut.udn.vn', '7480201'],
            ['102210042', 'Lê Hoàng Nam', '102210042@sv.dut.udn.vn', '7480201'],
            ['102210078', 'Phạm Quốc Bảo', '102210078@sv.dut.udn.vn', '7480201'],
            ['102210103', 'Hoàng Nhật Minh', '102210103@sv.dut.udn.vn', '7480201'],
            // Khóa 22 — CNTT Nhật (7480201A)
            ['102220008', 'Võ Thị Mai', '102220008@sv.dut.udn.vn', '7480201A'],
            ['102220021', 'Đặng Văn Hùng', '102220021@sv.dut.udn.vn', '7480201A'],
            ['102220055', 'Bùi Thanh Trúc', '102220055@sv.dut.udn.vn', '7480201A'],
            ['102220067', 'Ngô Đức Thịnh', '102220067@sv.dut.udn.vn', '7480201A'],
            // Khóa 23 — KHDL & AI (7480201B)
            ['102230004', 'Lý Thị Hương', '102230004@sv.dut.udn.vn', '7480201B'],
            ['102230019', 'Mai Xuân Phúc', '102230019@sv.dut.udn.vn', '7480201B'],
            ['102230033', 'Đỗ Gia Huy', '102230033@sv.dut.udn.vn', '7480201B'],
            ['102230048', 'Châu Bảo Ngọc', '102230048@sv.dut.udn.vn', '7480201B'],
            ['102230061', 'Trịnh Minh Đức', '102230061@sv.dut.udn.vn', '7480201B'],
            ['102230077', 'Phan Thị Yến', '102230077@sv.dut.udn.vn', '7480201B'],
            // Alias demo README / check-in
            ['SV001', 'Sinh viên Demo 1', 'student1@cca.test', '7480201'],
            ['SV002', 'Sinh viên Demo 2', 'student2@cca.test', '7480201'],
            ['SV003', 'Sinh viên Demo 3', 'student3@cca.test', '7480201B'],
        ];

        $students = [];

        foreach ($cohorts as [$code, $name, $email, $major]) {
            $students[] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name.' ('.$major.')',
                    'code' => $code,
                    'role' => UserRole::Student,
                    'password' => self::PASSWORD,
                ],
            );
        }

        return $students;
    }

    /**
     * @param  array<string, Subject>  $subjects
     * @param  array<string, User>  $lecturers
     * @return list<CourseSection>
     */
    private function seedCourseSections(array $subjects, array $lecturers): array
    {
        $plans = [
            ['IT008-01', 'Lập trình Web — nhóm 01 (CNTT)', 'IT008', 'GV001'],
            ['IT008-02', 'Lập trình Web — nhóm 02 (CNTT Nhật)', 'IT008', 'GV-DEMO'],
            ['IT003-01', 'Cơ sở dữ liệu — nhóm 01', 'IT003', 'GV002'],
            ['IT002-01', 'CTDL & GT — nhóm 01', 'IT002', 'GV003'],
            ['IT010-01', 'Trí tuệ nhân tạo — nhóm AI', 'IT010', 'GV005'],
            ['IT011-01', 'Học máy — nhóm AI', 'IT011', 'GV007'],
            ['IT012-01', 'Khoa học dữ liệu — nhóm AI', 'IT012', 'GV005'],
            ['IT005-01', 'Mạng máy tính — nhóm 01', 'IT005', 'GV004'],
            ['IT009-01', 'An toàn thông tin — nhóm 01', 'IT009', 'GV006'],
            ['CT101-01', 'Lập trình Web', 'CT101', 'GV-DEMO'],
        ];

        $sections = [];

        foreach ($plans as [$code, $name, $subjectCode, $lecturerCode]) {
            $sections[] = CourseSection::query()->updateOrCreate(
                ['code' => $code],
                [
                    'subject_id' => $subjects[$subjectCode]->id,
                    'name' => $name,
                    'semester' => self::SEMESTER,
                    'lecturer_id' => $lecturers[$lecturerCode]->id,
                ],
            );
        }

        return $sections;
    }

    /**
     * @param  list<CourseSection>  $sections
     * @param  list<User>  $students
     */
    private function seedEnrollments(array $sections, array $students): void
    {
        // Phân bổ: SV CNTT/Nhật vào lớp web/CSDL/mạng; SV AI vào lớp AI/ML/KHDL + web demo
        $byMajorHint = [
            '7480201' => ['IT008-01', 'IT003-01', 'IT002-01', 'IT005-01', 'CT101-01'],
            '7480201A' => ['IT008-02', 'IT003-01', 'IT009-01', 'CT101-01'],
            '7480201B' => ['IT010-01', 'IT011-01', 'IT012-01', 'IT008-01', 'CT101-01'],
        ];

        $sectionMap = collect($sections)->keyBy('code');

        foreach ($students as $student) {
            $major = '7480201';
            if (str_contains($student->name, '7480201A')) {
                $major = '7480201A';
            } elseif (str_contains($student->name, '7480201B')) {
                $major = '7480201B';
            }

            foreach ($byMajorHint[$major] as $sectionCode) {
                $section = $sectionMap->get($sectionCode);
                if (! $section) {
                    continue;
                }

                Enrollment::query()->firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_section_id' => $section->id,
                    ],
                );
            }
        }
    }
}
