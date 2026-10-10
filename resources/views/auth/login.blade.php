@extends('layouts.guest')

@section('title', 'Đăng nhập')
@section('page', 'login')

@section('content')
    <h1 class="mb-1 text-xl font-semibold text-gray-800">Đăng nhập</h1>
    <p class="mb-6 text-sm text-gray-500">Campus Class Attendance</p>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form class="space-y-4" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div>
            <label for="email" class="ht-label">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', 'admin@dut.udn.vn') }}" required class="ht-input">
        </div>
        <div>
            <label for="password" class="ht-label">Mật khẩu</label>
            <input type="password" name="password" id="password" value="password" required class="ht-input">
        </div>
        <div class="flex items-center gap-2">
            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <label for="remember" class="text-sm text-gray-600">Ghi nhớ đăng nhập</label>
        </div>
        <button type="submit" class="ht-btn-primary w-full">Đăng nhập</button>
        <p class="text-xs text-gray-500">Demo: <code>admin@dut.udn.vn</code> / <code>lecturer@cca.test</code> — password <code>password</code></p>
    </form>
@endsection
