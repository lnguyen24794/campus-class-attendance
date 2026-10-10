<form method="POST" action="{{ $action }}" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @if (!empty($method))
        @method($method)
    @endif

    <div>
        <label class="mb-1 block text-sm font-medium" for="code">Mã GV</label>
        <input id="code" name="code" value="{{ old('code', $lecturer?->code) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="name">Họ tên</label>
        <input id="name" name="name" value="{{ old('name', $lecturer?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $lecturer?->email) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="password">Mật khẩu {{ $lecturer ? '(để trống nếu giữ nguyên)' : '' }}</label>
        <input id="password" type="password" name="password" {{ $lecturer ? '' : 'required' }}
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>

    <div class="flex gap-3 pt-2">
        <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Lưu</button>
        <a href="{{ route('lecturers.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Hủy</a>
    </div>
</form>
