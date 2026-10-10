@props(['headers' => []])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="border-b border-slate-100 bg-slate-50 text-slate-500">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3 font-medium">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
