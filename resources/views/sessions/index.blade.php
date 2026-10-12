@extends('layouts.app')

@section('title', 'Lịch sử điểm danh')
@section('page', 'sessions-history')

@section('content')
    <div id="sessions-history-root" class="ht-page">
        <section class="ht-card p-4">
            <a href="{{ route('sessions.create') }}" class="ht-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Mở buổi mới
            </a>
        </section>

        <section class="ht-card p-4">
            <button type="button" id="session-search-toggle" class="ht-btn-primary" aria-expanded="true">
                <svg id="session-search-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
                Tìm kiếm
            </button>

            <div id="session-search-panel" class="mt-5">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="session-search-q" class="ht-label">Tìm kiếm</label>
                        <input type="text" id="session-search-q" class="ht-input" placeholder="Tìm theo tiêu đề, mã lớp…">
                    </div>
                    <div>
                        <label for="session-search-status" class="ht-label">Trạng thái</label>
                        <select id="session-search-status" class="ht-input">
                            <option value="">Tất cả</option>
                            <option value="open">Đang mở</option>
                            <option value="closed">Đã đóng</option>
                        </select>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                    <button type="button" id="session-search-btn" class="ht-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Tìm kiếm
                    </button>
                    <button type="button" id="session-search-clear" class="ht-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Hủy
                    </button>
                </div>
            </div>
        </section>

        <section class="ht-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="ht-table min-w-full">
                    <thead>
                        <tr>
                            <th class="w-14">STT</th>
                            <th>Tiêu đề</th>
                            <th>Lớp học phần</th>
                            <th>Tiết</th>
                            <th>Thời gian mở</th>
                            <th>Số SV</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="sessions-history-tbody">
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Đang tải…</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="sessions-history-pagination" class="border-t border-gray-200 px-4 py-3"></div>
        </section>
    </div>
@endsection
