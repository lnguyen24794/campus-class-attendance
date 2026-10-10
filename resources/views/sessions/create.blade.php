@extends('layouts.app')

@section('title', 'Điểm danh QR')
@section('page', 'sessions-create')

@section('content')
    <div class="ht-page">
        <section class="ht-card p-4">
            <a href="{{ route('sessions.index') }}" class="ht-btn-secondary">← Lịch sử điểm danh</a>
        </section>

        <section class="ht-card max-w-xl p-5">
            <p class="mb-5 text-sm text-gray-500">Chọn lớp học phần và mở buổi để hiện mã QR cho sinh viên.</p>

            <form id="session-create-form" class="space-y-4">
                <div>
                    <label for="course_section_id" class="ht-label">Lớp học phần</label>
                    <select id="course_section_id" name="course_section_id" required class="ht-input">
                        <option value="">Đang tải…</option>
                    </select>
                </div>
                <div>
                    <label for="period_id" class="ht-label">Tiết học</label>
                    <select id="period_id" name="period_id" class="ht-input">
                        <option value="">Đang tải…</option>
                    </select>
                </div>
                <div>
                    <label for="title" class="ht-label">Tiêu đề buổi</label>
                    <input id="title" name="title" value="Buổi điểm danh" required class="ht-input">
                </div>
                <div>
                    <label for="duration_minutes" class="ht-label">Thời lượng buổi (phút)</label>
                    <input id="duration_minutes" type="number" min="30" max="240" name="duration_minutes" value="45" class="ht-input">
                    <p id="duration_hint" class="mt-1 text-xs text-gray-500">
                        Tự điền theo giờ bắt đầu–kết thúc của tiết đã chọn (mỗi tiết ~45 phút nếu chưa chọn tiết).
                    </p>
                </div>
                <div>
                    <label for="late_after_minutes" class="ht-label">Đúng giờ trong vòng (phút)</label>
                    <input id="late_after_minutes" type="number" min="0" max="120" name="late_after_minutes" value="15" class="ht-input">
                    <p class="mt-1 text-xs text-gray-500">
                        Điểm danh trong khoảng này = <strong>Đúng giờ</strong>; sau đó = <strong>Đi muộn</strong>.
                    </p>
                </div>
                <button type="submit" class="ht-btn-primary">
                    Mở buổi &amp; hiện QR
                </button>
            </form>
        </section>
    </div>
@endsection
