<form method="POST" action="{{ $action }}" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @if (!empty($method))
        @method($method)
    @endif

    <div>
        <label class="mb-1 block text-sm font-medium" for="code">Mã môn</label>
        <input id="code" name="code" value="{{ old('code', $subject?->code) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="name">Tên môn</label>
        <input id="name" name="name" value="{{ old('name', $subject?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="credits">Số tín chỉ</label>
        <input id="credits" type="number" min="1" max="10" name="credits" value="{{ old('credits', $subject?->credits ?? 3) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" for="description">Mô tả</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $subject?->description) }}</textarea>
    </div>

    <div class="flex gap-3 pt-2">
        <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Lưu</button>
        <a href="{{ route('subjects.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Hủy</a>
    </div>
</form>
