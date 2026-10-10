@extends('layouts.app')

@section('title', 'Live điểm danh')
@section('page', 'sessions-live')

@section('content')
    <div id="session-live-root" class="ht-page" data-session-id="{{ $sessionId }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('sessions.index') }}" class="mb-2 inline-flex text-sm text-primary-600 hover:underline">← Lịch sử điểm danh</a>
                <h2 id="session-title" class="text-lg font-semibold text-gray-800">…</h2>
                <p id="session-meta" class="mt-1 text-sm text-gray-500">Đang tải…</p>
            </div>
            <button type="button" id="refresh-qr-btn" class="ht-btn-secondary">Làm mới QR</button>
        </div>

        <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
            <section class="ht-card p-5 text-center">
                <div id="qr-box" class="mx-auto flex min-h-[280px] items-center justify-center [&>svg]:mx-auto">
                    <span class="text-sm text-gray-400">Đang tạo QR…</span>
                </div>
                <p id="check-in-url" class="mt-3 break-all text-xs text-primary-700"></p>
                <div class="mt-3 flex flex-wrap justify-center gap-2">
                    <button type="button" id="copy-check-in-url" class="ht-btn-ghost !text-xs">
                        Sao chép link điểm danh
                    </button>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    Sinh viên quét QR → nhập <strong>MSSV</strong> (vd: 102210001) → xác nhận.
                </p>
            </section>

            <section class="ht-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                    <h2 class="font-semibold text-gray-800">Danh sách realtime</h2>
                    <span id="attendance-count" class="ht-badge-primary">0</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="ht-table min-w-full">
                        <thead>
                            <tr>
                                <th>Mã SV</th>
                                <th>Họ tên</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-rows"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
