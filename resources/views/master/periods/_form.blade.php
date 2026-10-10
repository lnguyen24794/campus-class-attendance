@php
    $starts = old('starts_at', $period ? \Illuminate\Support\Str::of($period->starts_at)->substr(0, 5) : '07:00');
    $ends = old('ends_at', $period ? \Illuminate\Support\Str::of($period->ends_at)->substr(0, 5) : '07:50');
@endphp

<form method="POST" action="{{ $action }}" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @if (!empty($method))
        @method($method)
    @endif

    <div>
        <label class="mb-1 block text-sm font-medium" for="number">Số tiết</label>
        <input id="number" type="number" min="1" max="20" name="number" value="{{ old('number', $period?->number) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="name">Tên tiết</label>
        <input id="name" name="name" value="{{ old('name', $period?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium" for="starts_at">Bắt đầu</label>
            <input id="starts_at" type="time" name="starts_at" value="{{ $starts }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="ends_at">Kết thúc</label>
            <input id="ends_at" type="time" name="ends_at" value="{{ $ends }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Lưu</button>
        <a href="{{ route('periods.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Hủy</a>
    </div>
</form>
