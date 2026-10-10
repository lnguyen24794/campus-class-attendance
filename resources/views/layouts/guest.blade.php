<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CCA')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" data-page="@yield('page')">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex items-center justify-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">CCA</span>
                <span class="text-lg font-semibold text-gray-800">Class Attendance</span>
            </div>
            @yield('content')
        </div>
    </main>
</body>
</html>
