@extends('layouts.app')

@section('title', 'Tổng quan')
@section('page', 'dashboard')

@section('content')
    <div id="dashboard-root" class="ht-page">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['students', 'Sinh viên', route('students.index')],
                ['lecturers', 'Giảng viên', route('lecturers.index')],
                ['subjects', 'Môn học', route('subjects.index')],
                ['periods', 'Tiết học', route('periods.index')],
            ] as [$key, $label, $url])
                <a href="{{ $url }}" class="ht-card block p-4 transition hover:border-primary-200 hover:shadow">
                    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                    <p data-count="{{ $key }}" class="mt-2 text-3xl font-bold text-gray-800">…</p>
                </a>
            @endforeach
        </div>

        <section class="ht-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                <h2 class="text-base font-semibold text-gray-800">Buổi đang mở</h2>
                <p class="text-xs text-gray-400">UC02 chưa triển khai — recreate từ docs/use-cases</p>
            </div>
            <div class="overflow-x-auto">
                <table class="ht-table min-w-full">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Lớp</th>
                            <th>Tiết</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="open-sessions-tbody">
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Đang tải…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
