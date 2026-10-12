<!DOCTYPE html>
<html lang="vi" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CCA') — Campus Class Attendance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-50" data-page="@yield('page')">
    @php
        $nav = [
            ['dashboard', 'Tổng quan', route('dashboard'), 'M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6z'],
            ['students.*', 'Quản lý Sinh viên', route('students.index'), 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'],
            ['lecturers.*', 'Quản lý Giảng viên', route('lecturers.index'), 'M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z'],
            ['subjects.*', 'Quản lý Môn học', route('subjects.index'), 'M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z'],
            ['periods.*', 'Quản lý Tiết học', route('periods.index'), 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'],
            ['sessions.index,sessions.live', 'Lịch sử điểm danh', route('sessions.index'), 'M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z'],
            ['sessions.create', 'Mở buổi điểm danh', route('sessions.create'), 'M3 4a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V5a1 1 0 00-1-1H3zm2 3h10v1H5V7zm0 3h6v1H5v-1z'],
        ];
        $user = auth()->user();
    @endphp

    {{-- Sidebar (admin-frontend MainSidebar style) --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform lg:translate-x-0"
           aria-label="Sidebar">
        <div class="flex h-14 items-center border-b border-gray-200 px-4">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-xs font-bold text-white">CCA</span>
                <span class="truncate text-base font-semibold text-gray-800">Class Attendance</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Menu</p>
            <ul class="space-y-1">
                @foreach ($nav as [$pattern, $label, $url, $icon])
                    @php $active = request()->routeIs(...array_map('trim', explode(',', $pattern))); @endphp
                    <li>
                        <a href="{{ $url }}"
                           class="ht-nav-item {{ $active ? 'ht-nav-item-active' : '' }}">
                            <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-primary-600' : 'text-gray-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $icon }}" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-gray-900/40 lg:hidden"></div>

    <div class="flex min-h-screen flex-col lg:ml-64">
        {{-- Top bar (admin-frontend TopAppbar) --}}
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white">
            <div class="flex h-14 items-center justify-between gap-3 px-3 sm:px-5">
                <div class="flex min-w-0 items-center gap-2">
                    <button type="button" id="sidebar-toggle"
                            class="inline-flex rounded-md p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-800"
                            aria-label="Toggle sidebar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h1 class="truncate text-base font-semibold text-gray-800 sm:text-lg">@yield('title', 'CCA')</h1>
                </div>

                @auth
                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-700">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="ht-btn-ghost !px-2.5 !py-1.5 text-xs">Đăng xuất</button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggle = document.getElementById('sidebar-toggle');
            if (!sidebar || !toggle) return;

            const open = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay?.classList.remove('hidden');
            };
            const close = () => {
                if (window.matchMedia('(min-width: 1024px)').matches) return;
                sidebar.classList.add('-translate-x-full');
                overlay?.classList.add('hidden');
            };

            toggle.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) open();
                else close();
            });
            overlay?.addEventListener('click', close);
        })();
    </script>
</body>
</html>
