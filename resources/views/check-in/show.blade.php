@extends('layouts.guest')

@section('title', 'Điểm danh QR')
@section('page', 'check-in')

@section('content')
    <div id="check-in-root" data-qr-token="{{ $qrToken }}">
        <h1 class="text-xl font-semibold text-gray-800">Điểm danh lớp</h1>
        <p id="check-in-meta" class="mt-1 text-sm text-gray-500">Đang tải buổi học…</p>

        <div id="check-in-success" class="mt-4 hidden rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"></div>
        <div id="check-in-expired" class="mt-4 hidden rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Mã QR đã hết hạn hoặc buổi chưa mở. Hãy quét lại mã mới trên màn hình lớp học.
        </div>

        <form id="check-in-form" class="mt-6 hidden space-y-4">
            <div>
                <label for="identifier" class="ht-label">Mã sinh viên hoặc email</label>
                <input id="identifier" name="identifier" required autofocus
                       placeholder="VD: 102210001 hoặc student1@cca.test"
                       class="ht-input">
            </div>
            <button type="submit" class="ht-btn-primary w-full">
                Xác nhận điểm danh
            </button>
        </form>
    </div>
@endsection
